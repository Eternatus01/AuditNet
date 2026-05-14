<?php

namespace App\Http\Requests;

use Closure;
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
            'urls.*' => ['required', 'string', 'max:2048', $this->httpUrlRule(), 'distinct'],
        ];
    }

    private function httpUrlRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (!is_string($value)) {
                $fail('Введите корректные URL сайтов');

                return;
            }

            $parsed = parse_url($value);
            if (!$parsed || !isset($parsed['scheme'], $parsed['host']) || $parsed['host'] === '') {
                $fail('Введите корректные URL сайтов');

                return;
            }

            if (!in_array(strtolower($parsed['scheme']), ['http', 'https'], true)) {
                $fail('Введите корректные URL сайтов');
            }
        };
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
