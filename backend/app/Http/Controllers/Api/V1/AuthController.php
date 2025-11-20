<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\LoginUserRequest;

class AuthController extends Controller
{
    public function register(StoreUserRequest $request){
        return 'Register';
    }

    public function login(LoginUserRequest $request){
        return 'Login';

    }

    public function logout(Request $request){
        return 'Logout';

    }
}
