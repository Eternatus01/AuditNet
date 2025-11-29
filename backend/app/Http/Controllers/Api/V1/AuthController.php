<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(StoreUserRequest $request){
        try {
            $data = $request->only(['name', 'email', 'password']);

            $user = User::create($data);

            Auth::login($user);

            return response()->json([
                'user' => new UserResource($user),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка регистрации. Попробуйте позже.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function login(LoginUserRequest $request){
        try {
            $credentials = $request->only(['email', 'password']);

            if(!Auth::attempt($credentials, true)){
                return response()->json([
                    'message' => 'Неверное имя пользователя или пароль'
                ], 401);
            }

            $user = Auth::user();

            $request->session()->regenerate();

            return response()->json([
                'user' => new UserResource($user),
            ]);

        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'Ошибка при попытке входа. Попробуйте позднее.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function logout(){
        try {
            Auth::guard('web')->logout();
            
            request()->session()->invalidate();
            
            request()->session()->regenerateToken();
    
            return response()->json([
                'message' => 'Вы успешно вышли'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка выхода. Попробуйте позже.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
