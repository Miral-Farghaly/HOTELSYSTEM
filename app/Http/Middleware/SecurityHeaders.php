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
            "default-src 'self' localhost:8888; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' localhost:8888; " .
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com localhost:8888; " .
            "font-src 'self' fonts.gstatic.com data:; " .
            "img-src 'self' data: https: blob:; " .
            "connect-src 'self' localhost:8888 ws://localhost:8888 wss://localhost:8888; " .
            "frame-src 'self' localhost:8888;"
        );

        return $response;
    }
} 