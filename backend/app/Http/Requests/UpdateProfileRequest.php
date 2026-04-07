<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'             => 'sometimes|string|min:2|max:255',
            'email'            => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'current_password' => 'required_with:password|string',
            'password'         => 'sometimes|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.min'                  => 'Имя должно содержать минимум 2 символа',
            'name.max'                  => 'Имя не должно превышать 255 символов',
            'email.email'               => 'Введите корректный email',
            'email.unique'              => 'Этот email уже занят',
            'current_password.required_with' => 'Укажите текущий пароль для смены пароля',
            'password.min'              => 'Новый пароль должен содержать минимум 8 символов',
            'password.confirmed'        => 'Пароли не совпадают',
        ];
    }
}
