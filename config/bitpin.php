<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bitpin API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Bitpin API client.
    | All values can be overridden via environment variables.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Your API Key and Secret Key from the Bitpin website/app (API section).
    | These are used to authenticate and obtain access/refresh tokens.
    |
    */
    'api_key' => env('BITPIN_API_KEY'),
    'secret_key' => env('BITPIN_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URLs
    |--------------------------------------------------------------------------
    |
    | The base URLs for REST and WebSocket APIs.
    | api.bitpin.ir is marked as temporarily unavailable in the docs.
    |
    */
    'base_url' => env('BITPIN_BASE_URL', 'https://api.bitpin.org'),
    'websocket_url' => env('BITPIN_WEBSOCKET_URL', 'wss://centrifugo.bitpin.org/connection/websocket'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Timeout and retry settings for the Guzzle HTTP client.
    |
    */
    'timeout' => env('BITPIN_TIMEOUT', 30), // seconds
    'connect_timeout' => env('BITPIN_CONNECT_TIMEOUT', 10), // seconds
    'retries' => env('BITPIN_RETRIES', 3),
    'retry_delay' => env('BITPIN_RETRY_DELAY', 1000), // milliseconds

    /*
    |--------------------------------------------------------------------------
    | Token Management
    |--------------------------------------------------------------------------
    |
    | Access tokens expire in 15 minutes, refresh tokens in 30 days.
    | The client will automatically refresh expired access tokens.
    |
    */
    'auto_refresh_token' => env('BITPIN_AUTO_REFRESH_TOKEN', true),
    'token_refresh_buffer' => env('BITPIN_TOKEN_REFRESH_BUFFER', 60), // seconds before expiry to refresh

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Default rate limits per the Bitpin API documentation.
    | These are for reference - the client doesn't enforce them locally.
    |
    */
    'rate_limits' => [
        'public_default' => 300, // per minute per IP
        'authenticate' => 200,   // per day
        'refresh_token' => 200,  // per day
        'currencies' => [
            'anonymous' => 10000, // per day
            'authenticated' => 200, // per minute
        ],
        'markets' => [
            'anonymous' => 10000,
            'authenticated' => 200,
        ],
        'networks' => [
            'anonymous' => 10000,
            'authenticated' => 200,
        ],
        'tickers' => 80, // per minute
        'orderbook' => 60, // per minute
        'matches' => 60, // per minute
        'candles' => 300, // per minute per IP
        'commissions' => 1000, // per day
        'wallet_networks_v2' => 60, // per minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Request Options
    |--------------------------------------------------------------------------
    |
    | Default headers and options for all requests.
    |
    */
    'default_headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'User-Agent' => 'Bitpin-API-PHP-SDK/1.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable request/response logging for debugging.
    |
    */
    'logging' => [
        'enabled' => env('BITPIN_LOGGING_ENABLED', false),
        'channel' => env('BITPIN_LOG_CHANNEL', 'default'),
        'level' => env('BITPIN_LOG_LEVEL', 'debug'),
    ],

];