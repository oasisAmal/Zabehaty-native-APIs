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

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch index names
    |--------------------------------------------------------------------------
    */
    'elasticsearch_index_names' => [
        'products' => env('SEARCH_ELASTICSEARCH_INDEX_NAME_PRODUCTS', 'products_ae_testing'),
        'shops' => env('SEARCH_ELASTICSEARCH_INDEX_NAME_SHOPS', 'shops_ae_testing'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch suggest fields
    |--------------------------------------------------------------------------
    */
    'elasticsearch_suggest_fields' => [
        'products' => [
            'name',
            'name_en',
            'description',
            'description_en',
        ],
        'shops' => [
            'name',
        ],
    ],
];
