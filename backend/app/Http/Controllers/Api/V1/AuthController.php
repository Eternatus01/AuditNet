<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(StoreUserRequest $request){
        try {
            $data = $request->only(['name', 'email', 'password']);

            $user = User::create($data);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user'=> [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка регистрации. Попробуйте позже.',
                'debug' => $e->getMessage(), // только для dev
            ], 500);
        }
    }

    public function login(LoginUserRequest $request){
        try {
            $credentials = $request->only(['email', 'password']);

            if(!Auth::attempt($credentials)){
                return response()->json([
                    'message' => 'Неверное имя пользователя или пароль'
                ], 401);
            }

            $user = User::where('email', $request->email)->first();
            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при попытке входа. Попробуйте позднее.',
                'debug' => $e->getMessage(), // только для dev
            ], 500);
        }
    }

    public function logout(){
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Пользователь не авторизован'
                ], 401);
            }

            Auth::user()->currentAccessToken()->delete();
    
            return response()->json([
                'message' => 'Вы успешно вышли'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка выхода. Попробуйте позже.',
                'debug' => $e->getMessage(), // только для dev
            ], 500);
        }
        
    }
}
