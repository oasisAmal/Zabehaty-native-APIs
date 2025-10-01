<?php

return [

    /**
     * SMS
     */
    'default_sms_service' => 'smscountry',
    'sms_global' => [
        'url' => 'https://api.smsglobal.com/http-api.php',
        'user_name' => 'hlhwv3o4',
        'password' => 'A1LS2p2U',
        'api_key' => '406fb1a7875b8ea1685e00454ba86010',
        'api_secert' => 'a801c4efa3aec096aa99e2d629ab64f5',
        'source' => 'Thahabi-App'
    ],
    'smscountry' => [
        'url' => 'https://restapi.smscountry.com/v0.1/Accounts',
        'auth_key' => 'K1izWHxGQMZKm8tyFEV1',
        'auth_token' => 'W2374w1iJlqDLFeFPGxK6AO6gn5xVvwav4VeI1sE',
        'source' => 'Thahabi-App'
    ],
    'dreams_sms' => [
        'url' => 'https://www.dreams.sa/index.php/api/sendsms/',
        'user' => 'thahabi',
        'secret_key' => '418b54eee2eaa0c4ff8f7ecac1408f16a3eabccf5febbc68e108b26c1f3b1bae',
        'sender' => 'THAHABBI',
    ], // for Saudi Arabia

    /**
     * Whatsapp
     */
    'default_whatsapp_service' => 'twilio',
    'twilio' => [
        'account_id' => [
            'zabehaty_app' => env('TWILIO_ACCOUNT_SID_ZABEHATY_APP'),
            'halal_app' => env('TWILIO_ACCOUNT_SID_HALAL_APP'),
        ],
        'token' => [
            'zabehaty_app' => env('TWILIO_AUTH_TOKEN_ZABEHATY_APP'),
            'halal_app' => env('TWILIO_AUTH_TOKEN_HALAL_APP'),
        ],
        'whatsapp_number' => [
            'zabehaty_app' => env('TWILIO_WHATSAPP_NUMBER_ZABEHATY_APP'),
            'halal_app' => env('TWILIO_WHATSAPP_NUMBER_HALAL_APP'),
        ],
    ],

    /**
     * Email
     */
    'smtp_email' => [
        'mail_host' => 'sandbox.smtp.mailtrap.io',
        'mail_port' => '2525',
        'mail_username' => 'bc83ede68aa0bf',
        'password' => '18969c5cb86c22',
        'mail_encryption' => 'tls',
        'from_address' => 'thahabi52@gmail.com',
        'from_name' => 'Thahabi'
    ],

    /**
     * Google
     */
    'google' => [
        'api_key' => 'AIzaSyCxhKX7Dkdk9UDckhnWy1QwWE9FE806kuw',
    ],

    /**
     * FCM
     */
    'fcm' => [
        'service_accounts' => [
            'zabehaty_app' => storage_path('app/firebase/firebase-service-zabehaty-app-account.json'),
            'halal_app' => storage_path('app/firebase/firebase-service-halal-app-account.json')
        ],
        'project_id' => [
            'zabehaty_app' => 'zabehaty-98bce',
            'halal_app' => 'halal-bca9c'
        ],
        'image_url' => [
            'zabehaty_app' => 'http://zabe7ti.website/img/logo.png',
            'halal_app' => 'http://zabe7ti.website/img/logo.png'
        ],
        'color' => [
            'zabehaty_app' => '#135725',
            'halal_app' => '#135725'
        ],
        'key' => [
            'zabehaty_app' => 'AAAACtAJ9NU:APA91bHLBvurghpvXglSX4PthNYTyU5QspiTcLjfCdtsGRc1CkEYknyEVPZZBRQ-qZHJYpq0YcEQDUSZxBuX0PYVgEzsZD56hPrXKq62f_c07evu_3RZM05cKn4ubxyuHKDRwhvxhGx-',
            'halal_app' => 'AAAACtAJ9NU:APA91bHLBvurghpvXglSX4PthNYTyU5QspiTcLjfCdtsGRc1CkEYknyEVPZZBRQ-qZHJYpq0YcEQDUSZxBuX0PYVgEzsZD56hPrXKq62f_c07evu_3RZM05cKn4ubxyuHKDRwhvxhGx-',
        ],
    ],

    /**
     * S3
     */
    's3' => [
        'url' => env('S3_URL', 'https://zabehaty.s3.us-east-2.amazonaws.com'),
    ],

    /**
     * Social Login
     */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID', '46439986389-fqt9r0atago27aktu7ken1cllqacsk6e.apps.googleusercontent.com'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', 'VgU6XuEHrV-gfaQvxemeNVmW'),
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID', '634005859736527'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET', 'a9c1f7e7e38896b7aefca2c71be7060e'),
    ],
    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID', 'com.OasisLiveStock.Zabehaty.Web'),
        'client_secret' => env('APPLE_CLIENT_SECRET', ''),
    ],
];
