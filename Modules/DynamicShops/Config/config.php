<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DynamicShops Module Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the DynamicShops module.
    | You can modify these settings to customize the behavior of the module.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure cache settings for the DynamicShops module.
    |
    */
    'cache' => [
        'enabled' => env('DYNAMIC_SHOPS_CACHE_ENABLED', false),
        'default_ttl' => env('DYNAMIC_SHOPS_CACHE_TTL', 3600), // 1 hour
    ]
];
