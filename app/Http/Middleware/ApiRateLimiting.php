<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiting
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $this->maxAttempts())) {
            return response()->json([
                'message' => 'Too Many Attempts.',
                'retry_after' => $this->limiter->availableIn($key)
            ], 429);
        }

        $this->limiter->hit($key, $this->decayMinutes() * 60);

        $response = $next($request);

        return $response->header('X-RateLimit-Limit', $this->maxAttempts())
            ->header('X-RateLimit-Remaining', $this->limiter->remaining($key, $this->maxAttempts()));
    }

    protected function resolveRequestSignature(Request $request): string
    {
        return sha1(implode('|', [
            $request->ip(),
            $request->userAgent(),
            $request->user()?->id ?? 'guest'
        ]));
    }

    protected function maxAttempts(): int
    {
        return config('api.rate_limits.max_attempts', 60);
    }

    protected function decayMinutes(): int
    {
        return config('api.rate_limits.decay_minutes', 1);
    }
} 