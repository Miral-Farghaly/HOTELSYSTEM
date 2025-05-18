<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap.xml file';

    public function handle()
    {
        $this->info('Generating sitemap...');

        try {
            $sitemapConfig = Config::get('seo.sitemap');
            
            if (!$sitemapConfig['enabled']) {
                $this->warn('Sitemap generation is disabled in config.');
                return 1;
            }

            // Create sitemap
            $sitemap = SitemapGenerator::create(config('app.url'))
                ->configureCrawler(function ($crawler) use ($sitemapConfig) {
                    return $crawler
                        ->setMaximumDepth(5)
                        ->ignoreRobots()
                        ->setMaximumCrawlCount(500);
                })
                ->hasCrawled(function (Url $url) use ($sitemapConfig) {
                    // Skip excluded routes
                    foreach ($sitemapConfig['excluded_routes'] as $pattern) {
                        if (str_is($pattern, $url->url)) {
                            return;
                        }
                    }

                    // Set default values
                    $url->setPriority(0.5)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY);

                    // Apply custom path settings
                    foreach ($sitemapConfig['custom_paths'] as $path => $settings) {
                        if (str_contains($url->url, $path)) {
                            $url->setPriority($settings['priority'])
                                ->setChangeFrequency($settings['changefreq']);
                            break;
                        }
                    }

                    return $url;
                });

            // Add custom URLs from config
            foreach ($sitemapConfig['custom_paths'] as $path => $settings) {
                $sitemap->add(
                    Url::create(url($path))
                        ->setPriority($settings['priority'])
                        ->setChangeFrequency($settings['changefreq'])
                        ->setLastModificationDate(Carbon::now())
                );
            }

            // Generate and save sitemap
            $sitemap->writeToFile(public_path('sitemap.xml'));

            // Submit to search engines if enabled
            if ($sitemapConfig['submit_to_search_engines']) {
                $sitemapUrl = url('sitemap.xml');
                foreach ($sitemapConfig['search_engines'] as $engine => $url) {
                    try {
                        $response = Http::get($url . urlencode($sitemapUrl));
                        if ($response->successful()) {
                            $this->info("Submitted sitemap to {$engine}");
                        } else {
                            $this->warn("Failed to submit sitemap to {$engine}");
                        }
                    } catch (\Exception $e) {
                        $this->error("Error submitting sitemap to {$engine}: " . $e->getMessage());
                    }
                }
            }

            $this->info('Sitemap generated successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error generating sitemap: ' . $e->getMessage());
            return 1;
        }
    }
} 