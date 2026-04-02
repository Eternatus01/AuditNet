<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseApiController
{
    public function register(StoreUserRequest $request)
    {
        try {
            $data = $request->only(['name', 'email', 'password']);

            $user = User::create($data);

            $token = $user->createToken(
                'auth-token',
                ['*'],
                now()->addDays(7)
            )->plainTextToken;

            return $this->successResponse([
                'user' => new UserResource($user),
                'token' => $token,
                'expires_at' => now()->addDays(7)->toIso8601String()
            ], null, 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка регистрации. Попробуйте позже.', 500);
        }
    }

    public function login(LoginUserRequest $request)
    {
        try {
            $credentials = $request->only(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                return $this->errorResponse('Неверное имя пользователя или пароль', 401);
            }

            $user = Auth::user();

            $user->tokens()->delete();

            $token = $user->createToken(
                'auth-token',
                ['*'],
                now()->addDays(7)
            )->plainTextToken;

            return $this->successResponse([
                'user' => new UserResource($user),
                'token' => $token,
                'expires_at' => now()->addDays(7)->toIso8601String()
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка при попытке входа. Попробуйте позднее.', 500);
        }
    }

    public function logout()
    {
        try {
            Auth::user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'Вы успешно вышли');
        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка выхода. Попробуйте позже.', 500);
        }
    }
}
