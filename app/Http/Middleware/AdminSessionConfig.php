<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionConfig
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin') || $request->is('admin/*')) {
            $production = app()->environment('production');
            config([
                'session.cookie' => $production ? '__Host-ia_admin' : 'ia_admin',
                'session.secure' => $production,
                'session.http_only' => true,
                'session.same_site' => 'lax',
                'session.domain' => null,
                'session.path' => '/',
                'session.expire_on_close' => true,
                'session.lifetime' => 15,
                'session.encrypt' => true,
            ]);
        }

        return $next($request);
    }
}
