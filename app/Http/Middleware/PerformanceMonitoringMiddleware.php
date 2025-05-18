<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerformanceMonitoringMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Start timing
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Handle request
        $response = $next($request);

        // Calculate metrics
        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $metrics = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'duration' => round(($endTime - $startTime) * 1000, 2), // in milliseconds
            'memory' => round(($endMemory - $startMemory) / 1024 / 1024, 2), // in MB
            'memory_peak' => round(memory_get_peak_usage() / 1024 / 1024, 2), // in MB
        ];

        // Log if duration exceeds threshold (e.g., 500ms)
        if ($metrics['duration'] > 500) {
            Log::channel('performance')->warning('Slow request detected', $metrics);
        } else {
            Log::channel('performance')->info('Request performance', $metrics);
        }

        // Add performance headers to response
        $response->headers->add([
            'X-Request-Duration' => $metrics['duration'].'ms',
            'X-Memory-Usage' => $metrics['memory'].'MB',
        ]);

        return $response;
    }
} 