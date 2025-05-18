<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for API rate limiting.
    |
    */

    'rate_limits' => [
        'max_attempts' => env('API_RATE_LIMIT_MAX_ATTEMPTS', 60),
        'decay_minutes' => env('API_RATE_LIMIT_DECAY_MINUTES', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Versions
    |--------------------------------------------------------------------------
    |
    | List of supported API versions
    |
    */

    'versions' => [
        'v1' => [
            'active' => true,
            'deprecated' => false,
            'sunset_date' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Documentation
    |--------------------------------------------------------------------------
    |
    | Configuration for API documentation
    |
    */

    'documentation' => [
        'enabled' => env('API_DOCUMENTATION_ENABLED', true),
        'path' => env('API_DOCUMENTATION_PATH', 'api/documentation'),
        'title' => env('API_DOCUMENTATION_TITLE', 'Hotel Management System API'),
        'version' => env('API_DOCUMENTATION_VERSION', '1.0.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    |
    | Security related configuration
    |
    */

    'security' => [
        'token_expiration' => env('API_TOKEN_EXPIRATION', 60 * 24), // 24 hours
        'refresh_token_expiration' => env('API_REFRESH_TOKEN_EXPIRATION', 60 * 24 * 7), // 7 days
        'encryption' => env('API_ENCRYPTION', 'AES-256-CBC'),
    ],
]; 