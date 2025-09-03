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
        ]
    ],
];
