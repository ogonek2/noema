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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'bunny' => [
        'storage_name' => env('BUNNY_STORAGE_NAME'),
        'storage_password' => env('BUNNY_STORAGE_PASSWORD'),
        'storage_region' => env('BUNNY_STORAGE_REGION', 'de'),
        'storage_api_url' => env('BUNNY_STORAGE_API_URL', 'https://storage.bunnycdn.com'),
        'cdn_url' => rtrim((string) env('BUNNY_CDN_URL', ''), '/'),
        'timeout' => (int) env('BUNNY_TIMEOUT_SECONDS', 20),
        'retry_times' => (int) env('BUNNY_RETRY_TIMES', 3),
        'retry_sleep_ms' => (int) env('BUNNY_RETRY_SLEEP_MS', 300),
    ],

];
