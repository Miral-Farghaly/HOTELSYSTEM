<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe Keys
    |--------------------------------------------------------------------------
    |
    | The Stripe publishable key and secret key give you access to Stripe's
    | API. The "publishable" key is typically used when interacting with
    | Stripe.js while the "secret" key accesses private API endpoints.
    |
    */

    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Configuration
    |--------------------------------------------------------------------------
    |
    | This is the default currency that will be used when generating charges
    | from your application. Of course, you are welcome to use any of the
    | various world currencies that are currently supported via Stripe.
    |
    */

    'currency' => env('STRIPE_CURRENCY', 'usd'),

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    |
    | Here you can configure various payment-related settings such as whether
    | to capture payments immediately or authorize only, and the default
    | statement descriptor that appears on customer credit card statements.
    |
    */

    'capture_method' => env('STRIPE_CAPTURE_METHOD', 'automatic'),
    'statement_descriptor' => env('STRIPE_STATEMENT_DESCRIPTOR', env('APP_NAME')),
    'statement_descriptor_suffix' => env('STRIPE_STATEMENT_DESCRIPTOR_SUFFIX'),

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Here you can specify which payment methods you want to support through
    | Stripe. By default, all payment methods are enabled.
    |
    */

    'payment_methods' => [
        'card',
        'ideal',
        'sepa_debit',
        'bancontact',
        'giropay',
        'sofort',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Here you can configure how Stripe operations should be logged.
    | The log channel specified here should be configured in config/logging.php
    |
    */

    'log_channel' => env('STRIPE_LOG_CHANNEL', 'payment'),
]; 