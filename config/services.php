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

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_API_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'model' => env('GEMINI_TTS_MODEL', 'gemini-3.1-flash-tts-preview'),
        'voice' => env('GEMINI_TTS_VOICE', 'Kore'),
        'timeout' => (int) env('GEMINI_API_TIMEOUT', 60),
        'connect_timeout' => (int) env('GEMINI_API_CONNECT_TIMEOUT', 10),
        'retries' => (int) env('GEMINI_API_RETRIES', 2),
        'retry_sleep_milliseconds' => (int) env('GEMINI_API_RETRY_SLEEP_MS', 300),
        'audio' => [
            'sample_rate' => (int) env('GEMINI_TTS_SAMPLE_RATE', 24000),
            'channels' => (int) env('GEMINI_TTS_CHANNELS', 1),
            'sample_width' => (int) env('GEMINI_TTS_SAMPLE_WIDTH', 2),
        ],
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

];
