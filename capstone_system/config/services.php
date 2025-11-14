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

    'random_forest_api' => [
        'base_url' => env('RANDOM_FOREST_API_URL', 'http://127.0.0.1:8001'),
        'api_key' => env('RANDOM_FOREST_API_KEY', 'random-api-key-2025'),
        'timeout' => env('RANDOM_FOREST_API_TIMEOUT', 30),
    ],

    'nutrition_api' => [
        'base_url' => env('LLM_API_URL', 'http://127.0.0.1:8002'),
        'timeout' => env('LLM_API_TIMEOUT', 30),
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'), // Google's test key
        'secret_key' => env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'), // Google's test key
    ],

];
