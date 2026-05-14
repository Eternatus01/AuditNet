<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComparisonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'urls' => ['required', 'array', 'min:2', 'max:5'],
            'urls.*' => ['required', 'url', 'max:2048', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'urls.required' => 'Добавьте сайты для сравнения',
            'urls.array' => 'Список сайтов должен быть массивом',
            'urls.min' => 'Добавьте минимум два сайта для сравнения',
            'urls.max' => 'Можно сравнить максимум пять сайтов за раз',
            'urls.*.url' => 'Введите корректные URL сайтов',
            'urls.*.distinct' => 'Сайты для сравнения не должны повторяться',
        ];
    }
}
