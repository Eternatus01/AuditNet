<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->configurePasswordReset();
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

    private function configurePasswordReset(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            $frontend = rtrim((string) config('app.frontend_url'), '/');

            return $frontend . '/reset-password?' . http_build_query([
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $frontend = rtrim((string) config('app.frontend_url'), '/');
            $url = $frontend . '/reset-password?' . http_build_query([
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            return (new MailMessage)
                ->subject('Восстановление пароля — AuditNet')
                ->greeting('Здравствуйте!')
                ->line('Вы получили это письмо, потому что для вашего аккаунта запрошено восстановление пароля.')
                ->action('Сбросить пароль', $url)
                ->line('Ссылка действительна ' . config('auth.passwords.users.expire', 60) . ' минут.')
                ->line('Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.');
        });
    }
}
