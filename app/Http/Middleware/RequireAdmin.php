<?php

namespace App\Http\Middleware;

use App\Models\AdminAccount;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->session()->get('admin.id');
        $admin = $id ? AdminAccount::find($id) : null;
        $started = $request->session()->get('admin.started_at', 0);
        $last = $request->session()->get('admin.last_activity', 0);
        if (! $admin || ! $admin->active || $admin->session_version !== $request->session()->get('admin.version') || time() - $started > 28800 || time() - $last > 900) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login');
        }
        $request->session()->put('admin.last_activity', time());
        $request->attributes->set('admin_account', $admin);

        return $next($request);
    }
}
