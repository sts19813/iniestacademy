<?php

namespace Tests\Feature;

use App\Models\AdminAccount;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): array
    {
        $totp = new Google2FA;
        $secret = $totp->generateSecretKey(32);
        $admin = AdminAccount::create(['username' => 'admin', 'password_hash' => Hash::make('a-secure-password-123'), 'totp_secret' => $secret]);

        return [$admin->fresh(), $totp, $secret];
    }

    public function test_admin_requires_authentication_origin_and_valid_second_factor(): void
    {
        [$admin, $totp, $secret] = $this->createAdmin();
        $this->get('/admin')->assertRedirect('/admin/acceso');
        $this->get('/admin/configuracion')->assertRedirect('/admin/acceso');
        $this->withHeader('Origin', 'https://attacker.example')->post('/admin/acceso', ['username' => 'admin', 'password' => 'a-secure-password-123', 'code' => $totp->getCurrentOtp($secret)])->assertForbidden();
        $this->withHeader('Origin', 'http://localhost:8000')->post('/admin/acceso', ['username' => 'admin', 'password' => 'wrong-password', 'code' => $totp->getCurrentOtp($secret)])->assertSessionHasErrors('login');
        $this->withHeader('Origin', 'http://localhost:8000')->post('/admin/acceso', ['username' => 'admin', 'password' => 'a-secure-password-123', 'code' => $totp->getCurrentOtp($secret)])->assertRedirect('/admin');
        $this->get('/admin')->assertOk()->assertSee('Inscripciones')->assertHeader('Cache-Control', 'no-store, private');
        $this->withHeader('Origin', 'http://localhost:8000')->post('/admin/salir')->assertRedirect('/admin/acceso');
        $this->get('/admin')->assertRedirect('/admin/acceso');
        $this->withHeader('Origin', 'http://localhost:8000')->post('/admin/acceso', ['username' => 'admin', 'password' => 'a-secure-password-123', 'code' => $totp->getCurrentOtp($secret)])->assertSessionHasErrors('login');
    }

    public function test_settings_require_reauthentication_and_update_public_prices(): void
    {
        [$admin, $totp, $secret] = $this->createAdmin();
        config(['registration.stripe_secret' => 'sk_test_dummy', 'registration.stripe_webhook_secret' => 'whsec_test']);
        $session = ['admin' => ['id' => $admin->id, 'version' => $admin->session_version, 'started_at' => time(), 'last_activity' => time()]];
        $values = ['regular_price' => '12000.00', 'promotion_price' => '9000.00', 'capacity' => '50', 'contact' => 'info@example.com', 'enabled' => '1', 'confirm_password' => 'wrong', 'confirm_code' => $totp->getCurrentOtp($secret)];
        $this->withSession($session)->withHeader('Origin', 'http://localhost:8000')->post('/admin/configuracion', $values)->assertSessionHasErrors('confirm_password');
        $this->assertSame(0, DB::table('site_settings')->count());
        $values['confirm_password'] = 'a-secure-password-123';
        $this->withSession($session)->withHeader('Origin', 'http://localhost:8000')->post('/admin/configuracion', $values)->assertRedirect('/admin/configuracion');
        $this->assertSame('1200000', DB::table('site_settings')->where('key', 'regular_amount')->value('value'));
        $this->get('/')->assertSee('$12,000')->assertSee('$9,000')->assertSee('25% de descuento');
        $this->assertDatabaseHas('admin_audit_events', ['event' => 'settings_updated', 'admin_account_id' => $admin->id]);
    }

    public function test_registration_details_are_private_and_audited(): void
    {
        [$admin] = $this->createAdmin();
        $registration = Registration::create(['buyer_name' => 'Tutor Example', 'buyer_email' => 'tutor@example.com', 'buyer_phone' => '9990000000', 'participant_name' => 'Jugador Example', 'participant_birth_date' => '2015-01-01', 'amount' => 820000, 'currency' => 'mxn']);
        $this->get('/admin/inscripciones/'.$registration->id)->assertRedirect('/admin/acceso');
        $session = ['admin' => ['id' => $admin->id, 'version' => $admin->session_version, 'started_at' => time(), 'last_activity' => time()]];
        $this->withSession($session)->get('/admin/inscripciones/'.$registration->id)->assertOk()->assertSee('Jugador Example')->assertSee('tutor@example.com')->assertHeader('Cache-Control', 'no-store, private');
        $this->assertDatabaseHas('admin_audit_events', ['admin_account_id' => $admin->id, 'event' => 'registration_viewed']);
    }

    public function test_expired_admin_session_cannot_access_data(): void
    {
        [$admin] = $this->createAdmin();
        $this->withSession(['admin' => ['id' => $admin->id, 'version' => $admin->session_version, 'started_at' => time(), 'last_activity' => time() - 901]])->get('/admin')->assertRedirect('/admin/acceso');
    }
}
