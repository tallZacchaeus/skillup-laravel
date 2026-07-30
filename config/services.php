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

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET', env('PAYSTACK_SECRET_KEY')),
        'payment_url' => rtrim(env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'), '/'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'resend' => [
        'api_key' => env('RESEND_API_KEY'),
        'from_address' => env('RESEND_FROM_ADDRESS'),
        'from_name' => env('RESEND_FROM_NAME', 'SkillUp Edtech'),
        'base_url' => env('RESEND_BASE_URL', 'https://api.resend.com'),
        'webhook_secret' => env('RESEND_WEBHOOK_SECRET'),
    ],

    'zeptomail' => [
        'api_key' => env('ZEPTOMAIL_API_KEY'),
        'from_address' => env('ZEPTOMAIL_FROM_ADDRESS'),
        'from_name' => env('ZEPTOMAIL_FROM_NAME'),
        'base_url' => env('ZEPTOMAIL_BASE_URL', 'https://api.zeptomail.com/v1.1'),
    ],

    'whatsapp' => [
        'phone_number_id' => env('WHATSAPP_BUSINESS_PHONE_NUMBER_ID'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'api_version' => env('WHATSAPP_API_VERSION', 'v20.0'),
        'base_url' => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com'),
    ],

    'discourse' => [
        'base_url' => env('DISCOURSE_BASE_URL'),
        'sso_secret' => env('DISCOURSE_SSO_SECRET'),
        'api_key' => env('DISCOURSE_API_KEY'),
        'api_username' => env('DISCOURSE_API_USERNAME'),
    ],

];
