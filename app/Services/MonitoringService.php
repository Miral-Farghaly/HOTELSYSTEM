<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Reservation;
use App\Models\MaintenanceLog;
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

    public function getRoomMetrics(): array
    {
        return Cache::remember('room_metrics', 3600, function () {
            return [
                'total_rooms' => Room::count(),
                'available_rooms' => Room::available()->count(),
                'maintenance_rooms' => Room::where('is_maintenance', true)->count(),
                'occupancy_rate' => $this->calculateOccupancyRate(),
                'maintenance_stats' => $this->getMaintenanceStats(),
            ];
        });
    }

    public function getReservationMetrics(): array
    {
        return Cache::remember('reservation_metrics', 3600, function () {
            return [
                'total_reservations' => Reservation::count(),
                'active_reservations' => $this->getActiveReservations(),
                'upcoming_reservations' => $this->getUpcomingReservations(),
                'revenue_stats' => $this->getRevenueStats(),
            ];
        });
    }

    public function getSystemHealth(): array
    {
        return [
            'queue_size' => $this->getQueueSize(),
            'failed_jobs' => $this->getFailedJobsCount(),
            'disk_usage' => $this->getDiskUsage(),
            'cache_hit_rate' => $this->getCacheHitRate(),
            'average_response_time' => $this->getAverageResponseTime(),
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
        return Cache::remember('avg_response_time', 300, function () {
            return DB::table('request_logs')
                ->whereDate('created_at', today())
                ->avg('response_time') ?? 0;
        });
    }

    protected function getErrorRate(): float
    {
        $total = Cache::get('total_requests', 0);
        $errors = Cache::get('error_requests', 0);
        
        return $total > 0 ? ($errors / $total) * 100 : 0;
    }

    private function calculateOccupancyRate(): float
    {
        $totalRooms = Room::count();
        if ($totalRooms === 0) return 0;

        $occupiedRooms = Reservation::whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>=', now())
            ->count();

        return round(($occupiedRooms / $totalRooms) * 100, 2);
    }

    private function getMaintenanceStats(): array
    {
        return [
            'scheduled' => MaintenanceLog::where('status', 'scheduled')->count(),
            'in_progress' => MaintenanceLog::where('status', 'in_progress')->count(),
            'completed' => MaintenanceLog::where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->count(),
            'average_completion_time' => $this->getAverageMaintenanceTime(),
        ];
    }

    private function getActiveReservations(): int
    {
        return Reservation::whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>=', now())
            ->count();
    }

    private function getUpcomingReservations(): int
    {
        return Reservation::whereDate('check_in', '>', now())
            ->whereDate('check_in', '<=', now()->addDays(7))
            ->count();
    }

    private function getRevenueStats(): array
    {
        return [
            'daily' => $this->calculateRevenue('day'),
            'weekly' => $this->calculateRevenue('week'),
            'monthly' => $this->calculateRevenue('month'),
            'yearly' => $this->calculateRevenue('year'),
        ];
    }

    private function calculateRevenue(string $period): float
    {
        return Reservation::where('status', 'confirmed')
            ->when($period === 'day', fn($q) => $q->whereDate('created_at', today()))
            ->when($period === 'week', fn($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($period === 'month', fn($q) => $q->whereMonth('created_at', now()->month))
            ->when($period === 'year', fn($q) => $q->whereYear('created_at', now()->year))
            ->sum('total_amount');
    }

    private function getQueueSize(): int
    {
        return DB::table('jobs')->count();
    }

    private function getFailedJobsCount(): int
    {
        return DB::table('failed_jobs')->count();
    }

    private function getDiskUsage(): array
    {
        $storage = storage_path();
        $total = disk_total_space($storage);
        $free = disk_free_space($storage);
        $used = $total - $free;

        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'percentage' => round(($used / $total) * 100, 2),
        ];
    }

    private function getCacheHitRate(): float
    {
        $hits = Cache::get('cache_hits', 0);
        $misses = Cache::get('cache_misses', 0);
        $total = $hits + $misses;

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    private function getAverageMaintenanceTime(): float
    {
        return MaintenanceLog::where('status', 'completed')
            ->whereNotNull('end_date')
            ->whereMonth('completed_at', now()->month)
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, start_date, end_date)')) ?? 0;
    }
} 