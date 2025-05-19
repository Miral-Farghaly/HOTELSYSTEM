<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use App\Models\Room;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap.';

    public function handle()
    {
        $this->info('Generating sitemap...');

        $sitemap = SitemapGenerator::create(config('app.url'))
            ->hasCrawled(function (Url $url) {
                if ($url->segment(1) === 'admin') {
                    return;
                }

                if ($url->segment(1) === 'api') {
                    return;
                }

                return $url;
            })
            ->getSitemap();

        // Add dynamic routes manually
        Room::all()->each(function (Room $room) use ($sitemap) {
            $sitemap->add(
                Url::create("/rooms/{$room->id}")
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(0.8)
            );
        });

        // Add static routes
        $staticRoutes = [
            '/' => 1.0,
            '/rooms' => 0.9,
            '/about' => 0.7,
            '/contact' => 0.7,
            '/terms' => 0.5,
            '/privacy' => 0.5,
        ];

        foreach ($staticRoutes as $route => $priority) {
            $sitemap->add(
                Url::create($route)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority($priority)
            );
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully.');
    }
} 