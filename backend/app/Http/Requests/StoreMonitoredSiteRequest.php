<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonitoredSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'url', 'max:2048'],
            'name' => ['nullable', 'string', 'max:255'],
            'schedule_day' => ['required', 'integer', 'between:1,7'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'URL обязателен для заполнения',
            'url.url' => 'Введите корректный URL',
            'url.max' => 'URL слишком длинный (максимум 2048 символов)',
            'schedule_day.required' => 'Выберите день недели',
            'schedule_day.between' => 'День недели должен быть от 1 (Пн) до 7 (Вс)',
        ];
    }
}
