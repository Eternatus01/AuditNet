<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AuditController;
use App\Http\Controllers\Api\V1\SecurityController;

// Получение текущего пользователя (использует web guard + session)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['web', 'auth:web']);

// Auth роуты БЕЗ аутентификации (для login/register)
// ВАЖНО: добавляем 'web' middleware для инициализации сессии
Route::prefix('auth')->middleware(['web', 'throttle:api'])->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

// Auth роуты С аутентификацией (logout)
Route::prefix('auth')->middleware(['web', 'throttle:api', 'auth:web'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Audit роуты (требуют аутентификацию)
Route::prefix('audit')->middleware(['web', 'throttle:api', 'auth:web'])->group(function () {
    Route::post('/analyze', [AuditController::class, 'analyze']);
    Route::post('/security-audit', [SecurityController::class, 'analyze']);
    Route::get('/history', [AuditController::class, 'history']);
    Route::get('/history/{id}', [AuditController::class, 'show']);
});

