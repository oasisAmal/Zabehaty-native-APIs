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
        'default_ttl' => env('HOMEPAGE_CACHE_TTL', 3600), // 1 hour
        'section_ttl' => [
            'banner' => env('HOMEPAGE_BANNER_CACHE_TTL', 1800), // 30 minutes
            'products' => env('HOMEPAGE_PRODUCTS_CACHE_TTL', 900), // 15 minutes
            'categories' => env('HOMEPAGE_CATEGORIES_CACHE_TTL', 7200), // 2 hours
            'featured' => env('HOMEPAGE_FEATURED_CACHE_TTL', 1800), // 30 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Section Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default settings for different section types.
    |
    */
    'sections' => [
        'banner' => [
            'auto_play' => true,
            'interval' => 5000,
            'show_indicators' => true,
        ],
        'products' => [
            'limit' => 10,
            'show_price' => true,
            'show_discount' => true,
        ],
        'categories' => [
            'limit' => 8,
            'show_icons' => true,
            'show_names' => true,
        ],
        'featured' => [
            'limit' => 6,
            'show_banners' => true,
            'show_products' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API response settings.
    |
    */
    'api' => [
        'include_metadata' => env('HOMEPAGE_INCLUDE_METADATA', false),
        'max_sections' => env('HOMEPAGE_MAX_SECTIONS', 20),
        'default_language' => env('HOMEPAGE_DEFAULT_LANGUAGE', 'en'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Configuration
    |--------------------------------------------------------------------------
    |
    | Configure image handling settings.
    |
    */
    'images' => [
        'banner_width' => env('HOMEPAGE_BANNER_WIDTH', 1200),
        'banner_height' => env('HOMEPAGE_BANNER_HEIGHT', 400),
        'story_width' => env('HOMEPAGE_STORY_WIDTH', 300),
        'story_height' => env('HOMEPAGE_STORY_HEIGHT', 300),
        'quality' => env('HOMEPAGE_IMAGE_QUALITY', 85),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configure performance-related settings.
    |
    */
    'performance' => [
        'lazy_load_images' => env('HOMEPAGE_LAZY_LOAD_IMAGES', true),
        'compress_responses' => env('HOMEPAGE_COMPRESS_RESPONSES', true),
        'preload_critical_data' => env('HOMEPAGE_PRELOAD_CRITICAL_DATA', true),
    ],
];