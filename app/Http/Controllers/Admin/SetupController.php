<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAccount;
use App\Services\AdminAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class SetupController extends Controller
{
    public function start()
    {
        abort_if(AdminAccount::exists(), 404);

        return view('admin.setup-start');
    }

    public function redeem(Request $request)
    {
        $data = $request->validate(['token' => ['required', 'regex:/^[a-f0-9]{64}$/']]);
        $entry = DB::transaction(function () use ($data) {
            if (AdminAccount::exists()) {
                return null;
            }
            $token = DB::table('admin_setup_tokens')->where('token_hash', hash('sha256', $data['token']))->whereNull('used_at')->where('expires_at', '>', now())->first();
            if (! $token) {
                return null;
            }
            $updated = DB::table('admin_setup_tokens')->where('id', $token->id)->whereNull('used_at')->where('expires_at', '>', now())->update(['used_at' => now()]);

            return $updated ? $token : null;
        });
        if (! $entry) {
            throw ValidationException::withMessages(['token' => 'El enlace ya se usó o venció. Solicita uno nuevo.']);
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('admin_setup', ['username' => $entry->username, 'secret' => (new Google2FA)->generateSecretKey(32), 'started_at' => time()]);

        return redirect()->route('admin.setup.form');
    }

    public function form(Request $request)
    {
        $setup = $this->pending($request);

        return view('admin.setup-form', ['username' => $setup['username'], 'secret' => $setup['secret']]);
    }

    public function finish(Request $request)
    {
        $setup = $this->pending($request);
        $data = $request->validate([
            'password' => ['required', 'string', 'min:14', 'max:256', 'confirmed'],
            'code' => ['required', 'digits:6'],
        ], ['password.min' => 'La contraseña debe tener al menos 14 caracteres.', 'password.confirmed' => 'Las contraseñas no coinciden.']);
        $step = (new Google2FA)->verifyKeyNewer($setup['secret'], $data['code'], 0, 1);
        if ($step === false) {
            throw ValidationException::withMessages(['code' => 'El código no es correcto. Revisa tu aplicación de autenticación.']);
        }
        DB::transaction(function () use ($setup, $data, $step) {
            abort_if(AdminAccount::exists(), 404);
            $admin = AdminAccount::create(['username' => $setup['username'], 'password_hash' => Hash::make($data['password']), 'totp_secret' => $setup['secret'], 'last_totp_step' => $step]);
            AdminAudit::record($admin->id, 'initial_admin_created');
        });
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Cuenta creada. Espera al siguiente código de tu autenticador para iniciar sesión.');
    }

    private function pending(Request $request): array
    {
        $setup = $request->session()->get('admin_setup');
        abort_if(AdminAccount::exists() || ! is_array($setup) || time() - ($setup['started_at'] ?? 0) > 900, 404);

        return $setup;
    }
}
