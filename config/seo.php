<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SEO Defaults
    |--------------------------------------------------------------------------
    |
    | Default values for SEO meta tags used throughout the application.
    |
    */
    'defaults' => [
        'title' => env('APP_NAME', 'Luxury Hotel'),
        'title_separator' => ' - ',
        'description' => 'Experience luxury and comfort at our premium hotel. Book your stay today for the best rates and exclusive amenities.',
        'keywords' => 'hotel, luxury hotel, hotel booking, accommodation, rooms, suites',
        'robots' => 'index, follow',
        'author' => env('APP_NAME', 'Luxury Hotel'),
        'canonical' => env('APP_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Open Graph
    |--------------------------------------------------------------------------
    |
    | Open Graph meta tags for social media sharing
    |
    */
    'og' => [
        'site_name' => env('APP_NAME', 'Luxury Hotel'),
        'type' => 'website',
        'image' => '/images/hotel-og-image.jpg',
        'image:width' => 1200,
        'image:height' => 630,
    ],

    /*
    |--------------------------------------------------------------------------
    | Twitter Card
    |--------------------------------------------------------------------------
    |
    | Twitter Card meta tags for Twitter sharing
    |
    */
    'twitter' => [
        'card' => 'summary_large_image',
        'site' => '@luxuryhotel', // Your hotel's Twitter handle
        'creator' => '@luxuryhotel',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for sitemap generation
    |
    */
    'sitemap' => [
        'enabled' => true,
        'cache_enabled' => true,
        'cache_length' => 1440, // 24 hours in minutes
        'submit_to_search_engines' => true,
        'search_engines' => [
            'google' => 'https://www.google.com/webmasters/tools/ping?sitemap=',
            'bing' => 'https://www.bing.com/ping?sitemap=',
        ],
        'excluded_routes' => [
            'admin.*',
            'api.*',
            'login',
            'register',
            'password.*',
        ],
        'custom_paths' => [
            // Add custom paths that should be included in sitemap
            '/rooms' => [
                'priority' => 0.9,
                'changefreq' => 'daily',
            ],
            '/booking' => [
                'priority' => 0.8,
                'changefreq' => 'daily',
            ],
            '/special-offers' => [
                'priority' => 0.7,
                'changefreq' => 'weekly',
            ],
            '/contact' => [
                'priority' => 0.5,
                'changefreq' => 'monthly',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Structured Data
    |--------------------------------------------------------------------------
    |
    | JSON-LD structured data for rich snippets
    |
    */
    'structured_data' => [
        'hotel' => [
            '@type' => 'Hotel',
            'name' => env('APP_NAME', 'Luxury Hotel'),
            'description' => 'A luxury hotel offering premium accommodation and exceptional service.',
            'starRating' => [
                '@type' => 'Rating',
                'ratingValue' => '5',
            ],
            'priceRange' => '$$$',
            'amenityFeature' => [
                ['@type' => 'LocationFeatureSpecification', 'name' => 'Swimming Pool'],
                ['@type' => 'LocationFeatureSpecification', 'name' => 'Free WiFi'],
                ['@type' => 'LocationFeatureSpecification', 'name' => 'Restaurant'],
                ['@type' => 'LocationFeatureSpecification', 'name' => 'Spa'],
                ['@type' => 'LocationFeatureSpecification', 'name' => 'Fitness Center'],
            ],
        ],
    ],
]; 