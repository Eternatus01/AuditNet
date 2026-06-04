<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Недействительная ссылка для сброса пароля',
            'email.required' => 'Укажите email',
            'email.email' => 'Введите корректный email',
            'password.required' => 'Укажите новый пароль',
            'password.min' => 'Пароль должен содержать минимум 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ];
    }
}
