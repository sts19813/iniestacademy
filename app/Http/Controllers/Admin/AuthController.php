<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAccount;
use App\Services\AdminAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function show()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate(['username' => ['required', 'string', 'max:64'], 'password' => ['required', 'string', 'max:256'], 'code' => ['required', 'digits:6']]);
        $username = strtolower(trim($data['username']));
        $accountKey = 'admin-login-user:'.hash('sha256', $username);
        $ipKey = 'admin-login-ip:'.hash('sha256', $request->ip());
        if (RateLimiter::tooManyAttempts($accountKey, 5) || RateLimiter::tooManyAttempts($ipKey, 5)) {
            $wait = max(RateLimiter::availableIn($accountKey), RateLimiter::availableIn($ipKey));

            return response()->view('admin.login', ['lockout' => true], 429)->header('Retry-After', max(1, $wait));
        }
        $admin = AdminAccount::where('username', $username)->first();
        $passwordOk = Hash::check($data['password'], $admin?->password_hash ?? '$2y$12$i/iyrBo8EURwFeArs.xRj.8vIR7/JuuLhz2rrfVndpb6L4wlM8qrS');
        $step = $admin && $passwordOk && $admin->active ? (new Google2FA)->verifyKeyNewer($admin->totp_secret, $data['code'], $admin->last_totp_step ?? 0, 1) : false;
        $updated = $step !== false && $admin ? AdminAccount::whereKey($admin->id)->where(function ($query) use ($step) {
            $query->whereNull('last_totp_step')->orWhere('last_totp_step', '<', $step);
        })->update(['last_totp_step' => $step]) : 0;
        if (! $updated) {
            RateLimiter::hit($accountKey, 900);
            RateLimiter::hit($ipKey, 900);
            AdminAudit::record($admin?->id, 'login_failed');

            return back()->withErrors(['login' => 'No se pudo iniciar sesión. Revisa tus datos.'])->onlyInput('username');
        }
        RateLimiter::clear($accountKey);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('admin', ['id' => $admin->id, 'version' => $admin->session_version, 'started_at' => time(), 'last_activity' => time()]);
        AdminAudit::record($admin->id, 'login_succeeded');

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        AdminAudit::record($request->attributes->get('admin_account')->id, 'logout');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
