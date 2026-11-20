<?php

return [
    'mail' => [
        'markdown' => [
            'theme' => 'default',
            'paths' => [
                resource_path('views/vendor/mail'),
            ],
        ],
    ],

    'maptiler' => [
        'key' => env('MAPTILER_KEY'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
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

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'checkout_enabled' => env('STRIPE_CHECKOUT_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    */
    'groq' => [
        'api_key' => env('GROQ_API_KEY', ''),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY', ''),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY', ''),
    ],

    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY', ''),
    ],

    'ai' => [
        'default_provider' => env('AI_DEFAULT_PROVIDER', 'groq'),
        'default_model' => env('AI_DEFAULT_MODEL', 'openai/gpt-oss-120b'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Bank / Bizum Details
    |--------------------------------------------------------------------------
    */
    'bank' => [
        'name' => env('BANK_NAME', 'Tu Banco'),
        'iban' => env('BANK_IBAN', 'ES00 0000 0000 0000 0000 0000'),
        'holder' => env('BANK_HOLDER', 'Costa Resin & Construction'),
    ],

    'bizum' => [
        'number' => env('BIZUM_NUMBER', '654195493'),
    ],

    'mapillary' => [
        'token' => env('MAPILLARY_TOKEN'),
    ],
];
