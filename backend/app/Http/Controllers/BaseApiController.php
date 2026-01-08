<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    protected function successResponse(mixed $data = null, string $message = null, int $code = 200): JsonResponse
    {
        $response = ['success' => true];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return response()->json($response, $code);
    }

    protected function errorResponse(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        if (config('app.debug') && $code >= 500) {
            $response['debug'] = true;
        }
        
        return response()->json($response, $code);
    }

    protected function getAuthenticatedUser(): ?\App\Models\User
    {
        return auth()->user();
    }

    protected function requireAuthenticatedUser(): \App\Models\User
    {
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            abort(401, 'Пользователь не авторизован');
        }
        
        return $user;
    }
}

