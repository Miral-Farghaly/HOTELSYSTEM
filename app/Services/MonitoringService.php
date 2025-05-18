<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonitoringService
{
    public function recordApiMetrics(string $endpoint, float $duration, int $statusCode): void
    {
        $key = "api_metrics:{$endpoint}:" . date('Y-m-d');
        
        $metrics = Cache::get($key, [
            'calls' => 0,
            'total_duration' => 0,
            'errors' => 0,
            'avg_duration' => 0,
        ]);

        $metrics['calls']++;
        $metrics['total_duration'] += $duration;
        $metrics['avg_duration'] = $metrics['total_duration'] / $metrics['calls'];
        
        if ($statusCode >= 400) {
            $metrics['errors']++;
        }

        Cache::put($key, $metrics, now()->addDays(7));
    }

    public function checkSystemHealth(): array
    {
        $health = [
            'database' => $this->checkDatabaseConnection(),
            'cache' => $this->checkCacheConnection(),
            'storage' => $this->checkStorageAccess(),
            'queue' => $this->checkQueueConnection(),
            'memory_usage' => $this->getMemoryUsage(),
        ];

        Log::channel('performance')->info('System health check', $health);

        return $health;
    }

    public function getApplicationMetrics(): array
    {
        return [
            'active_users' => $this->getActiveUsers(),
            'database_metrics' => $this->getDatabaseMetrics(),
            'cache_hit_ratio' => $this->getCacheHitRatio(),
            'average_response_time' => $this->getAverageResponseTime(),
            'error_rate' => $this->getErrorRate(),
        ];
    }

    protected function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::channel('error')->error('Database connection failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function checkCacheConnection(): bool
    {
        try {
            Cache::store()->get('test_key');
            return true;
        } catch (\Exception $e) {
            Log::channel('error')->error('Cache connection failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function checkStorageAccess(): bool
    {
        return is_writable(storage_path()) && is_writable(storage_path('logs'));
    }

    protected function checkQueueConnection(): bool
    {
        try {
            $connection = config('queue.default');
            return config("queue.connections.{$connection}.driver") !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getMemoryUsage(): array
    {
        return [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
        ];
    }

    protected function getActiveUsers(): int
    {
        return Cache::get('active_users', 0);
    }

    protected function getDatabaseMetrics(): array
    {
        return [
            'total_connections' => DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 0,
            'slow_queries' => DB::select('SHOW STATUS LIKE "Slow_queries"')[0]->Value ?? 0,
        ];
    }

    protected function getCacheHitRatio(): float
    {
        $hits = Cache::get('cache_hits', 0);
        $misses = Cache::get('cache_misses', 0);
        
        return $hits + $misses > 0 ? ($hits / ($hits + $misses)) * 100 : 0;
    }

    protected function getAverageResponseTime(): float
    {
        return Cache::get('avg_response_time', 0.0);
    }

    protected function getErrorRate(): float
    {
        $total = Cache::get('total_requests', 0);
        $errors = Cache::get('error_requests', 0);
        
        return $total > 0 ? ($errors / $total) * 100 : 0;
    }
} 