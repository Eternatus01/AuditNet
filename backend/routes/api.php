<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AuditController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->middleware(['throttle:api', 'auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::prefix('auth')->middleware(['throttle:api'])->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

Route::prefix('audit')->middleware(['throttle:api', 'auth:sanctum'])->group(function () {
    Route::post('/analyze', [AuditController::class, 'analyze']);
});

