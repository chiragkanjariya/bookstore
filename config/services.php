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

    'razorpay' => [
        'key' => env('RAZORPAY_KEY_ID'),
        'secret' => env('RAZORPAY_KEY_SECRET'),
    ],

    'courier' => [
        'provider' => env('COURIER_PROVIDER', 'shree_maruti'),
    ],

    'shiprocket' => [
        'enabled' => env('SHIPROCKET_ENABLED', false),
        'email' => env('SHIPROCKET_EMAIL'),
        'password' => env('SHIPROCKET_PASSWORD'),
    ],

    'shree_maruti' => [
        'enabled' => env('SHREE_MARUTI_ENABLED', true),
        'environment' => env('SHREE_MARUTI_ENVIRONMENT', 'beta'),
        'client_code' => env('SHREE_MARUTI_CLIENT_CODE'),
        'client_name' => env('SHREE_MARUTI_CLIENT_NAME'),
        'username' => env('SHREE_MARUTI_USERNAME'),
        'password' => env('SHREE_MARUTI_PASSWORD'),
        'secret_key_prod' => env('SHREE_MARUTI_SECRET_KEY_PROD'),
        'secret_key_beta' => env('SHREE_MARUTI_SECRET_KEY_BETA'),
    ],

];
