<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Redis;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Разрешаем всем (можно изменить при необходимости)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'nickname' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Проверка уникальности через Redis
                    if (Redis::exists("user:nickname:{$value}")) {
                        $fail('Nickname уже занят.');
                    }

                    // Дополнительная проверка в БД (на случай расхождения кэша)
                    if (\App\Models\User::where('nickname', $value)->exists()) {
                        Redis::set("user:nickname:{$value}", 'taken'); // Синхронизация
                        $fail('Nickname уже занят.');
                    }
                },
            ],
            'avatar' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
            ],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'nickname.required' => 'Никнейм обязателен для заполнения.',
            'nickname.string' => 'Никнейм должен быть строкой.',
            'nickname.max' => 'Никнейм не может превышать 255 символов.',
            'avatar.required' => 'Аватар обязателен для загрузки.',
            'avatar.image' => 'Файл должен быть изображением.',
            'avatar.mimes' => 'Допустимые форматы: jpeg, png, jpg, gif.',
            'avatar.max' => 'Размер аватара не должен превышать 2 МБ.',
        ];
    }
}
