<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Elasticsearch connection (Elastica client)
    |--------------------------------------------------------------------------
    | When running in Docker, set ELASTICSEARCH_HOST=elasticsearch (service name).
    | @see https://elastica.io/getting-started/installation.html
    */
    'hosts' => [
        implode('', [
            env('ELASTICSEARCH_SCHEME', 'http'),
            '://',
            env('ELASTICSEARCH_HOST', 'elasticsearch'),
            ':',
            env('ELASTICSEARCH_PORT', 9200),
        ]),
    ],

    'username' => env('ELASTICSEARCH_USER'),
    'password' => env('ELASTICSEARCH_PASSWORD'),

    'retryOnConflict' => (int) env('ELASTICSEARCH_RETRY_ON_CONFLICT', 0),
];
