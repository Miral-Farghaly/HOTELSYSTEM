<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Reservation;
use App\Models\MaintenanceLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;

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

    public function getSystemMetrics(): array
    {
        return [
            'bookings' => $this->getBookingMetrics(),
            'rooms' => $this->getRoomMetrics(),
            'system' => $this->getSystemHealthMetrics(),
            'performance' => $this->getPerformanceMetrics()
        ];
    }

    private function getRoomMetrics(): array
    {
        return Cache::remember('monitoring.rooms', 300, function () {
            return [
                'total' => Room::count(),
                'available' => Room::where('is_available', true)->count(),
                'maintenance' => Room::where('needs_maintenance', true)->count(),
                'occupancy_rate' => $this->calculateOccupancyRate(),
                'average_price' => Room::avg('price_per_night'),
                'maintenance_stats' => $this->getMaintenanceStats()
            ];
        });
    }

    private function getBookingMetrics(): array
    {
        return Cache::remember('monitoring.bookings', 300, function () {
            return [
                'total' => Booking::count(),
                'pending' => Booking::where('status', 'pending')->count(),
                'confirmed' => Booking::where('status', 'confirmed')->count(),
                'cancelled' => Booking::where('status', 'cancelled')->count(),
                'revenue_today' => Booking::whereDate('created_at', today())
                    ->where('status', 'confirmed')
                    ->sum('total_price'),
                'bookings_today' => Booking::whereDate('created_at', today())->count(),
                'active_reservations' => $this->getActiveReservations(),
                'upcoming_reservations' => $this->getUpcomingReservations(),
                'revenue_stats' => $this->getRevenueStats()
            ];
        });
    }

    private function getSystemHealthMetrics(): array
    {
        return [
            'database' => $this->checkDatabaseConnection(),
            'cache' => $this->checkCacheConnection(),
            'storage' => $this->checkStorageHealth(),
            'queue' => $this->checkQueueHealth(),
            'disk_usage' => $this->getDiskUsage()
        ];
    }

    private function getPerformanceMetrics(): array
    {
        return [
            'average_response_time' => $this->getAverageResponseTime(),
            'error_rate' => $this->getErrorRate(),
            'memory_usage' => memory_get_usage(true),
            'cpu_usage' => sys_getloadavg()[0],
            'cache_hit_rate' => $this->getCacheHitRate()
        ];
    }

    private function calculateOccupancyRate(): float
    {
        $totalRooms = Room::count();
        if ($totalRooms === 0) {
            return 0;
        }

        $occupiedRooms = Booking::where('status', 'confirmed')
            ->whereDate('check_in_date', '<=', now())
            ->whereDate('check_out_date', '>=', now())
            ->count();

        return ($occupiedRooms / $totalRooms) * 100;
    }

    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::error('Database connection failed: ' . $e->getMessage());
            return false;
        }
    }

    private function checkCacheConnection(): bool
    {
        try {
            Cache::store()->get('test');
            return true;
        } catch (\Exception $e) {
            Log::error('Cache connection failed: ' . $e->getMessage());
            return false;
        }
    }

    private function checkStorageHealth(): array
    {
        $storagePath = storage_path();
        return [
            'free_space' => disk_free_space($storagePath),
            'total_space' => disk_total_space($storagePath),
            'is_writable' => is_writable($storagePath)
        ];
    }

    private function checkQueueHealth(): array
    {
        return [
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'pending_jobs' => DB::table('jobs')->count()
        ];
    }

    private function getAverageResponseTime(): float
    {
        return Cache::remember('monitoring.avg_response_time', 60, function () {
            return DB::table('request_logs')
                ->whereDate('created_at', today())
                ->avg('response_time') ?? 0.0;
        });
    }

    private function getErrorRate(): float
    {
        return Cache::remember('monitoring.error_rate', 60, function () {
            $totalRequests = Cache::get('monitoring.total_requests', 0);
            if ($totalRequests === 0) {
                return 0;
            }

            $errorCount = Cache::get('monitoring.error_count', 0);
            return ($errorCount / $totalRequests) * 100;
        });
    }

    private function checkStorageAccess(): bool
    {
        return is_writable(storage_path()) && is_writable(storage_path('logs'));
    }

    private function checkQueueConnection(): bool
    {
        try {
            $connection = config('queue.default');
            return config("queue.connections.{$connection}.driver") !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getMemoryUsage(): array
    {
        return [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
        ];
    }

    private function getActiveUsers(): int
    {
        return Cache::get('active_users', 0);
    }

    private function getDatabaseMetrics(): array
    {
        return [
            'total_connections' => DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 0,
            'slow_queries' => DB::select('SHOW STATUS LIKE "Slow_queries"')[0]->Value ?? 0,
        ];
    }

    private function getCacheHitRatio(): float
    {
        $hits = Cache::get('cache_hits', 0);
        $misses = Cache::get('cache_misses', 0);
        
        return $hits + $misses > 0 ? ($hits / ($hits + $misses)) * 100 : 0;
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

    private function getMaintenanceStats(): array
    {
        return [
            'scheduled' => MaintenanceLog::where('status', 'scheduled')->count(),
            'in_progress' => MaintenanceLog::where('status', 'in_progress')->count(),
            'completed' => MaintenanceLog::whereMonth('completed_at', now()->month)
                ->where('status', 'completed')
                ->count(),
            'average_completion_time' => $this->getAverageMaintenanceTime()
        ];
    }

    private function getAverageMaintenanceTime(): float
    {
        return MaintenanceLog::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereMonth('completed_at', now()->month)
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, completed_at)')) ?? 0;
    }
} 