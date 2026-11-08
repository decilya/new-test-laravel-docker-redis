<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\User\RegisterUser\RegisterUserCommand;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на регистрацию пользователя.
 *
 * Отвечает ТОЛЬКО за валидацию формата входных данных.
 * Бизнес-валидация выполняется в слое Application.
 */
final class RegisterUserRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nickname' => ['required', 'string', 'min:3', 'max:32', 'regex:/^[a-zA-Z0-9_]+$/'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }

    /**
     * @return RegisterUserCommand
     */
    public function toCommand(): RegisterUserCommand
    {
        return new RegisterUserCommand(
            nickname: (string) $this->validated('nickname'),
            email: (string) $this->validated('email'),
            password: (string) $this->validated('password'),
            avatar: $this->file('avatar') // Теперь avatar обязателен
        );
    }
}
