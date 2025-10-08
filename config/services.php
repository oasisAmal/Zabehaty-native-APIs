<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Socialite OAuth Providers
    |--------------------------------------------------------------------------
    |
    | Configuration for OAuth providers used by Laravel Socialite
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID', '46439986389-fqt9r0atago27aktu7ken1cllqacsk6e.apps.googleusercontent.com'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', 'VgU6XuEHrV-gfaQvxemeNVmW'),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'http://localhost:8080/api/auth/google/callback'),
    ],

    'google_ios' => [
        'client_id' => env('GOOGLE_IOS_CLIENT_ID', '46439986389-e3usme8r97ndf2h998fj7lsqe9ch8mm4.apps.googleusercontent.com'),
        'client_secret' => env('GOOGLE_IOS_CLIENT_SECRET', ''),
        'redirect' => env('GOOGLE_IOS_REDIRECT_URI', 'http://localhost:8080/api/auth/google/callback'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID', 'com.OasisLiveStock.Zabehaty'),
        'client_secret' => env('APPLE_CLIENT_SECRET', 'eyJraWQiOiJWUjhSS0Y0TjRCIiwiYWxnIjoiRVMyNTYiLCJ0eXAiOiJKV1QifQ.eyJpYXQiOjE3NTkzODcyOTEuNTk4MzQzLCJleHAiOjE3NTkzODc1OTEuNTk4MzQzLCJzdWIiOiJjb20uT2FzaXNMaXZlU3RvY2suWmFiZWhhdHkiLCJhdWQiOiJodHRwczpcL1wvYXBwbGVpZC5hcHBsZS5jb20iLCJpc3MiOiI4VDlGNU1RVlJXIn0.LxSufe7CDXTYWe20koflvwk35AxnaVq4zGf4MMbQgaARJNGuMCZt-JWS_ptkMFSRxdCdl9RcLK5A2Z8ZBr_f9A'),
        'redirect' => env('APPLE_REDIRECT_URI'),
    ],

];
