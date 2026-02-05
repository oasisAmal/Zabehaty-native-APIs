<?php

return [
    'name' => 'Search',

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('SEARCH_CACHE_ENABLED', false),
        'default_ttl' => env('SEARCH_CACHE_TTL', 600), // 10 minutes
    ],
];
