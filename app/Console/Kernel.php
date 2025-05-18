<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Backup tasks
        $schedule->command('backup:clean')->daily()->at('00:00');
        $schedule->command('backup:run')->daily()->at('01:00');
        $schedule->command('backup:monitor')->daily()->at('02:00');
        $schedule->command('backup:clean')->weekly()->at('00:30');

        // Cache tasks
        $schedule->command('cache:warmup')->hourly();
        $schedule->command('cache:prune-stale')->daily();
        
        // Clear and rebuild specific caches during off-peak hours
        $schedule->command('cache:clear --tag=hotel_prices')->daily()->at('03:00')
            ->then(function () {
                \Artisan::call('cache:warmup');
            });
            
        // Monitor cache size and health
        $schedule->command('cache:monitor')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
