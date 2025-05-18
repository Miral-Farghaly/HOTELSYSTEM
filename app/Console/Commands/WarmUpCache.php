<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;

class WarmUpCache extends Command
{
    protected $signature = 'cache:warmup';
    protected $description = 'Warm up commonly accessed caches';

    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    public function handle()
    {
        $this->info('Starting cache warm-up...');

        try {
            $this->cacheService->warmUpCache();
            $this->info('Cache warm-up completed successfully!');
        } catch (\Exception $e) {
            $this->error('Cache warm-up failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
} 