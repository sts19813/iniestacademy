<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethodSafe()) {
            $source = $request->headers->get('Origin') ?: $request->headers->get('Referer');
            $parts = $source ? parse_url($source) : false;
            $expectedPort = $request->getPort();
            $sourcePort = $parts ? ($parts['port'] ?? (($parts['scheme'] ?? '') === 'https' ? 443 : 80)) : 0;
            if (! $parts || strtolower($parts['host'] ?? '') !== strtolower($request->getHost()) || ($parts['scheme'] ?? '') !== $request->getScheme() || $sourcePort !== $expectedPort) {
                abort(403);
            }
        }

        return $next($request);
    }
}
