<?php

namespace Tests\Feature;

use App\Models\AdminAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AdminSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_one_time_link_creates_admin_only_after_password_and_authenticator_verification(): void
    {
        $this->assertSame(0, Artisan::call('admin:activation-link', ['username' => 'admin']));
        preg_match('/#([a-f0-9]{64})/', Artisan::output(), $matches);
        $token = $matches[1];
        $this->assertDatabaseMissing('admin_setup_tokens', ['token_hash' => $token]);
        $this->assertDatabaseHas('admin_setup_tokens', ['token_hash' => hash('sha256', $token)]);
        $this->withHeader('Origin', 'http://localhost:8000')->post('/admin/activar', ['token' => $token])->assertRedirect('/admin/activar/cuenta');
        $this->withHeader('Origin', 'http://localhost:8000')->post('/admin/activar', ['token' => $token])->assertSessionHasErrors('token');
        $secret = session('admin_setup.secret');
        $this->assertNotEmpty($secret);
        $this->get('/admin/activar/cuenta')->assertOk()->assertSee('Crear tu acceso');
        $this->withHeader('Origin', 'http://localhost:8000')->post('/admin/activar/cuenta', ['password' => 'a-secure-password-123', 'password_confirmation' => 'a-secure-password-123', 'code' => '000000'])->assertSessionHasErrors('code');
        $this->assertSame(0, AdminAccount::count());
        $code = (new Google2FA)->getCurrentOtp($secret);
        $this->withHeader('Origin', 'http://localhost:8000')->post('/admin/activar/cuenta', ['password' => 'a-secure-password-123', 'password_confirmation' => 'a-secure-password-123', 'code' => $code])->assertRedirect('/admin/acceso');
        $admin = AdminAccount::where('username', 'admin')->firstOrFail();
        $this->assertTrue(Hash::check('a-secure-password-123', $admin->password_hash));
        $this->assertNotSame($secret, DB::table('admin_accounts')->where('id', $admin->id)->value('totp_secret'));
        $this->get('/admin/activar')->assertNotFound();
        $this->assertSame(1, AdminAccount::count());
    }

    public function test_expired_and_used_links_cannot_be_redeemed(): void
    {
        $token = str_repeat('a', 64);
        DB::table('admin_setup_tokens')->insert(['token_hash' => hash('sha256', $token), 'username' => 'admin', 'expires_at' => now()->subMinute(), 'created_at' => now()]);
        $this->withHeader('Origin', 'http://localhost:8000')->post('/admin/activar', ['token' => $token])->assertSessionHasErrors('token');
        $this->assertSame(0, AdminAccount::count());
        $this->assertNull(session('admin_setup'));
    }
}
