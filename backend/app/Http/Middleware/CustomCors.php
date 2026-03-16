<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomCors
{
    public function handle(Request $request, Closure $next): Response
    {
        // Разрешённые origins
        $allowedOrigins = [
            'http://91.142.74.132:5173',
            'http://91.142.74.132',
            'http://localhost:5173',
            'http://localhost',
        ];

        $origin = $request->header('Origin');

        // Если это preflight запрос (OPTIONS)
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', in_array($origin, $allowedOrigins) ? $origin : $allowedOrigins[0])
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        // Обрабатываем обычный запрос
        $response = $next($request);

        // Добавляем CORS заголовки к ответу
        return $response
            ->header('Access-Control-Allow-Origin', in_array($origin, $allowedOrigins) ? $origin : $allowedOrigins[0])
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
            ->header('Access-Control-Allow-Credentials', 'true');
    }
}
