<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\GenerateSitemap::class,
        Commands\CheckRoomMaintenance::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run maintenance check daily at 1 AM
        $schedule->command('rooms:check-maintenance')->dailyAt('01:00');

        // Clean up old records
        $schedule->command('model:prune', [
            '--model' => [MaintenanceLog::class],
        ])->monthly();

        // Update room availability cache
        $schedule->command('cache:rooms-availability')->hourly();

        // Process queued jobs
        $schedule->command('queue:work --stop-when-empty')->everyMinute();

        // Monitor system health
        $schedule->command('monitor:check-health')->everyFiveMinutes();

        // Backup database
        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->daily()->at('02:00');

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

        // Generate sitemap daily at midnight
        $schedule->command('sitemap:generate')
                ->daily()
                ->at('00:00')
                ->onSuccess(function () {
                    \Log::info('Sitemap generated successfully');
                })
                ->onFailure(function () {
                    \Log::error('Failed to generate sitemap');
                });
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
