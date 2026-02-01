<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AuditController;
use App\Http\Controllers\Api\V1\SecurityController;
use App\Http\Resources\UserResource;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String()
    ]);
});

Route::get('/user', function (Request $request) {
    $user = $request->user();
    
    if (!$user) {
        return response()->json([
            'message' => 'Не авторизован'
        ], 401);
    }
    
    return new UserResource($user);
})->middleware('auth:sanctum');

Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

Route::prefix('auth')->middleware(['throttle:api', 'auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::prefix('audit')->middleware(['throttle:api', 'auth:sanctum'])->group(function () {
    Route::post('/analyze', [AuditController::class, 'analyze'])->middleware('prevent.ssrf');
    Route::post('/security-audit', [SecurityController::class, 'analyze'])->middleware('prevent.ssrf');
    Route::get('/history', [AuditController::class, 'history']);
    Route::get('/history/{id}', [AuditController::class, 'show']);
    Route::get('/status/{id}', [AuditController::class, 'status']);
});


