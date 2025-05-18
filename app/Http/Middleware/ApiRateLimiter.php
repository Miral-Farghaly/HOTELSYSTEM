<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Cache\RateLimiting\Limit;

class ApiRateLimiter
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = optional($request->user())->id ?: $request->ip();
        
        $limits = [
            // Authenticated users get higher limits
            'auth' => [
                'points' => 100,
                'decay' => 60, // per minute
            ],
            // Guest users get lower limits
            'guest' => [
                'points' => 30,
                'decay' => 60,
            ],
        ];

        $config = $request->user() ? $limits['auth'] : $limits['guest'];

        $executed = RateLimiter::attempt(
            "api:{$key}",
            $config['points'],
            function() use ($next, $request) {
                return $next($request);
            },
            $config['decay']
        );

        if (!$executed) {
            return response()->json([
                'error' => 'Too Many Requests',
                'message' => 'API rate limit exceeded',
                'retry_after' => RateLimiter::availableIn("api:{$key}"),
            ], 429);
        }

        $response = $executed;

        // Add rate limit headers to response
        return $response->withHeaders([
            'X-RateLimit-Limit' => $config['points'],
            'X-RateLimit-Remaining' => RateLimiter::remaining("api:{$key}", $config['points']),
            'X-RateLimit-Reset' => RateLimiter::availableIn("api:{$key}"),
        ]);
    }
} 