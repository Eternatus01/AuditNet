<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends BaseApiController
{
    public function register(StoreUserRequest $request)
    {
        try {
            $data = $request->only(['name', 'email', 'password']);

            $user = User::create($data);

            Auth::login($user);

            return $this->successResponse(['user' => new UserResource($user)], null, 201);
        } catch (\Exception $e) {
            Log::error('Registration error', ['error' => $e->getMessage()]);
            return $this->errorResponse('Ошибка регистрации. Попробуйте позже.', 500);
        }
    }

    public function login(LoginUserRequest $request)
    {
        try {
            $credentials = $request->only(['email', 'password']);

            if (!Auth::attempt($credentials, true)) {
                return $this->errorResponse('Неверное имя пользователя или пароль', 401);
            }

            $user = Auth::user();

            $request->session()->regenerate();

            return $this->successResponse(['user' => new UserResource($user)]);

        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Ошибка при попытке входа. Попробуйте позднее.', 500);
        }
    }

    public function logout()
    {
        try {
            Auth::guard('web')->logout();

            request()->session()->invalidate();

            request()->session()->regenerateToken();

            return $this->successResponse(null, 'Вы успешно вышли');
        } catch (\Exception $e) {
            Log::error('Logout error', ['error' => $e->getMessage()]);
            return $this->errorResponse('Ошибка выхода. Попробуйте позже.', 500);
        }
    }
}
