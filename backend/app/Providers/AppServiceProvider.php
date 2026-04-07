<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json(['message' => 'Too many requests'], 429);
            });
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return response()->json([
                    'message' => 'Слишком много попыток входа. Попробуйте через минуту.',
                    'retry_after' => 60
                ], 429);
            });
        });

        RateLimiter::for('guest', function (Request $request) {
            return Limit::perMinutes(10, 5)->by($request->ip())->response(function () {
                return response()->json([
                    'message' => 'Слишком много запросов. Зарегистрируйтесь для снятия ограничений или попробуйте через 10 минут.',
                    'retry_after' => 600
                ], 429);
            });
        });
    }
}
