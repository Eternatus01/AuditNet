<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SecurityAuditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'url',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'URL обязателен для заполнения',
            'url.url' => 'Введите корректный URL',
            'url.max' => 'URL слишком длинный (максимум 2048 символов)',
        ];
    }
}

