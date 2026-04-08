<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Whatspie API Token
    |--------------------------------------------------------------------------
    |
    | Your Whatspie API token for authentication.
    | Get it from https://whatspie.com/dashboard
    |
    */

    'api_token' => env('WHATSPIE_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Whatspie API. You can override this for different
    | environments or testing purposes.
    |
    */

    'base_url' => env('WHATSPIE_API_URL', 'https://api.whatspie.com'),

    /*
    |--------------------------------------------------------------------------
    | Device Number
    |--------------------------------------------------------------------------
    |
    | Your registered WhatsApp device number in international format
    | without the + symbol (e.g., 6281234567890).
    |
    */

    'device' => env('WHATSPIE_DEVICE'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the webhook endpoint for receiving incoming messages.
    |
    */

    'webhook' => [
        'enabled' => env('WHATSPIE_WEBHOOK_ENABLED', true),
        'path' => env('WHATSPIE_WEBHOOK_PATH', '/whatspie/webhook'),
        'secret' => env('WHATSPIE_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure where local files are uploaded before sending to Whatspie.
    | The disk must be publicly accessible.
    |
    */

    'storage' => [
        'disk' => env('WHATSPIE_STORAGE_DISK', 'public'),
        'path' => env('WHATSPIE_STORAGE_PATH', 'whatspie'),
    ],
];
