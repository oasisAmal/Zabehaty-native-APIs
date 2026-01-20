<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DynamicCategories Module Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the DynamicCategories module.
    | You can modify these settings to customize the behavior of the module.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure cache settings for the DynamicCategories module.
    |
    */
    'cache' => [
        'enabled' => env('DYNAMIC_CATEGORIES_CACHE_ENABLED', true),
        'default_ttl' => env('DYNAMIC_CATEGORIES_CACHE_TTL', 600), // 10 minutes
    ]
];
