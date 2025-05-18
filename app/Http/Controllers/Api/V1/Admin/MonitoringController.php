<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\MonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MonitoringController extends Controller
{
    protected $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function health(): JsonResponse
    {
        $health = $this->monitoringService->checkSystemHealth();
        
        $statusCode = collect($health)->contains(false) ? 503 : 200;
        
        return response()->json([
            'status' => $statusCode === 200 ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => $health
        ], $statusCode);
    }

    public function metrics(): JsonResponse
    {
        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'metrics' => $this->monitoringService->getApplicationMetrics()
        ]);
    }

    public function logs(): JsonResponse
    {
        $logs = [
            'error_logs' => $this->getRecentLogs('error'),
            'security_logs' => $this->getRecentLogs('security'),
            'performance_logs' => $this->getRecentLogs('performance')
        ];

        return response()->json($logs);
    }

    public function errorStats(): JsonResponse
    {
        $stats = [
            'total_errors' => Cache::get('error_count', 0),
            'error_rate' => $this->monitoringService->getErrorRate(),
            'most_common_errors' => $this->getMostCommonErrors(),
            'recent_critical_errors' => $this->getRecentCriticalErrors()
        ];

        return response()->json($stats);
    }

    protected function getRecentLogs(string $channel, int $limit = 100): array
    {
        $logPath = storage_path("logs/{$channel}.log");
        
        if (!file_exists($logPath)) {
            return [];
        }

        $logs = [];
        $handle = fopen($logPath, "r");
        
        if ($handle) {
            $lines = [];
            while (($line = fgets($handle)) !== false) {
                array_push($lines, $line);
                if (count($lines) > $limit) {
                    array_shift($lines);
                }
            }
            fclose($handle);
            
            foreach ($lines as $line) {
                if (preg_match('/\[(.*?)\].*?({".*})/i', $line, $matches)) {
                    $logs[] = [
                        'timestamp' => $matches[1],
                        'data' => json_decode($matches[2], true)
                    ];
                }
            }
        }

        return $logs;
    }

    protected function getMostCommonErrors(): array
    {
        return Cache::remember('most_common_errors', now()->addMinutes(5), function () {
            return Log::channel('error')
                ->getLogger()
                ->getHandlers()[0]
                ->getRecords();
        });
    }

    protected function getRecentCriticalErrors(): array
    {
        return Cache::remember('recent_critical_errors', now()->addMinutes(5), function () {
            $logs = $this->getRecentLogs('error', 1000);
            return collect($logs)
                ->filter(function ($log) {
                    return isset($log['data']['level']) && $log['data']['level'] === 'critical';
                })
                ->take(10)
                ->values()
                ->all();
        });
    }
} 