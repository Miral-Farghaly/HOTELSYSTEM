<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $cspHeader = "
            default-src 'self' localhost:8889 localhost:5173;
            script-src 'self' 'unsafe-inline' 'unsafe-eval' localhost:8889 localhost:5173;
            style-src * 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com localhost:8889 localhost:5173;
            style-src-elem * 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com localhost:8889 localhost:5173;
            font-src * data: https://fonts.bunny.net https://fonts.gstatic.com;
            img-src 'self' data: blob: *;
            connect-src 'self' localhost:8889 localhost:5173 ws://localhost:8889 ws://localhost:5173;
            frame-src 'self';
            media-src 'self';
            object-src 'none';
            base-uri 'self';
            form-action 'self';
            worker-src 'self' blob:;
        ";

        // Remove any existing CSP headers first
        $response->headers->remove('Content-Security-Policy');
        
        // Set the new CSP header
        $response->headers->set('Content-Security-Policy', trim(preg_replace('/\s+/', ' ', $cspHeader)));

        return $response;
    }
} 