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

    'nova_poshta' => [
        'api_key' => env('NOVA_POSHTA_API_KEY'),
        'api_url' => env('NOVA_POSHTA_API_URL', 'https://api.novaposhta.ua/v2.0/json/'),
        'timeout' => (int) env('NOVA_POSHTA_TIMEOUT', 20),
        'verify_ssl' => filter_var(env('NOVA_POSHTA_VERIFY_SSL', env('APP_ENV') === 'production'), FILTER_VALIDATE_BOOLEAN),
    ],

    'liqpay' => [
        'public_key' => env('LIQPAY_PUBLIC_KEY'),
        'private_key' => env('LIQPAY_PRIVATE_KEY'),
        'sandbox' => filter_var(env('LIQPAY_SANDBOX', true), FILTER_VALIDATE_BOOLEAN),
        'currency' => env('LIQPAY_CURRENCY', 'UAH'),
        'checkout_url' => env('LIQPAY_CHECKOUT_URL', 'https://www.liqpay.ua/api/3/checkout'),
    ],

    'telegram' => [
        // Shared hosting often fails SSL verify to api.telegram.org — set TELEGRAM_VERIFY_SSL=false in .env
        'verify_ssl' => filter_var(env('TELEGRAM_VERIFY_SSL', false), FILTER_VALIDATE_BOOLEAN),
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
        // On local OpenServer/XAMPP, cURL may fail with "self-signed certificate in chain".
        'verify_ssl' => env('BUNNY_VERIFY_SSL', env('APP_ENV') === 'production'),
    ],

];
