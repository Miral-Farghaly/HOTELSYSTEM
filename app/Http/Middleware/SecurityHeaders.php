<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Content-Security-Policy', 
            "default-src 'self' localhost:8889; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' localhost:8889; " .
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com localhost:8889; " .
            "font-src 'self' fonts.gstatic.com data:; " .
            "img-src 'self' data: https: blob:; " .
            "connect-src 'self' localhost:8889 ws://localhost:8889 wss://localhost:8889; " .
            "frame-src 'self' localhost:8889;"
        );

        return $response;
    }
} 