<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HomePage Module Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the HomePage module.
    | You can modify these settings to customize the behavior of the module.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure cache settings for the HomePage module.
    |
    */
    'cache' => [
        'enabled' => env('HOMEPAGE_CACHE_ENABLED', true),
        'default_ttl' => env('HOMEPAGE_CACHE_TTL', 600), // 10 minutes
    ]
];