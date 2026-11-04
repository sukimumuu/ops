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

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        'chat_model' => env('GROQ_CHAT_MODEL', 'qwen/qwen3.8-27b'),
    ],

    'mihom' => [
        'dp_percentage' => env('MIHOM_DP_PERCENTAGE', 10),
    ],

    'xendit' => [
        'environment' => env('XENDIT_ENVIRONMENT', 'sandbox'),
        'sandbox_secret_key' => env('XENDIT_SANDBOX_SECRET_KEY'),
        'production_secret_key' => env('XENDIT_PRODUCTION_SECRET_KEY'),
        'sandbox_webhook_token' => env('XENDIT_SANDBOX_WEBHOOK_TOKEN'),
        'production_webhook_token' => env('XENDIT_PRODUCTION_WEBHOOK_TOKEN'),
        'virtual_account_channel_code' => env('XENDIT_DP_VA_CHANNEL_CODE', 'BNI'),
        'virtual_account_expiry_hours' => env('XENDIT_DP_VA_EXPIRY_HOURS', 24),
    ],

];
