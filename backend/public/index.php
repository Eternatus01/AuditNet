<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// CORS заголовки для всех запросов (добавлено ДО Laravel)
$allowedOrigins = [
    'http://91.142.74.132:5173',
    'http://91.142.74.132',
    'http://localhost:5173',
    'http://localhost',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: " . $allowedOrigins[0]);
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN, X-XSRF-TOKEN");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400");

// Обработка ТОЛЬКО OPTIONS запроса (preflight) для API роутов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS' && strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    http_response_code(200);
    exit;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
