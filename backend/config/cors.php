<?php

$allowedOrigins = array_values(array_unique(array_filter(array_map('trim', explode(
    ',',
    env('CORS_ALLOWED_ORIGINS', env('FRONTEND_URL', 'http://localhost:5173'))
)))));

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_unique(array_merge($allowedOrigins, [
        'http://localhost:5173',
        'http://localhost:5174',
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'https://auditnet.onrender.com',
        'http://xn--80aidlz3acc.xn--p1ai',
        'http://xn--80aidlz3acc.xn--p1ai:5173',
        'http://138.16.177.238',
        'http://138.16.177.238:5173',
    ]))),

    'allowed_origins_patterns' => [
        '/\.onrender\.com$/',
        '/^http:\/\/xn--80aidlz3acc\.xn--p1ai(:\d+)?$/',
        '/^http:\/\/138\.16\.177\.238(:\d+)?$/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // ВАЖНО: разрешаем отправку cookies

];

