<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    /*
    |--------------------------------------------------------------------------
    | Campaign Manager 360 (DFA Reporting API v5)
    |--------------------------------------------------------------------------
    |
    | Configuration for the CM360 API integration.
    | Access token should be obtained via OAuth2 flow.
    | Required scope: https://www.googleapis.com/auth/dfatrafficking
    |
    */
    'cm360' => [
        'access_token' => env('CM360_ACCESS_TOKEN'),
        'client_id' => env('CM360_CLIENT_ID'),
        'client_secret' => env('CM360_CLIENT_SECRET'),
        'refresh_token' => env('CM360_REFRESH_TOKEN'),
        'credentials_path' => env('CM360_CREDENTIALS_PATH'),
    ],

];
