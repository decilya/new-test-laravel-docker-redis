<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\User\RegisterUser\RegisterUserCommand;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на регистрацию пользователя.
 *
 * Отвечает ТОЛЬКО за валидацию формата входных данных (синтаксис).
 * Бизнес-валидация (уникальность никнейма и email) выполняется
 * в слое Application (RegisterUserHandler).
 */
final class RegisterUserRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации входных данных.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nickname' => ['required', 'string', 'min:3', 'max:32', 'regex:/^[a-zA-Z0-9_]+$/'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'], // Максимум 2MB
        ];
    }

    /**
     * Преобразует валидированные данные в DTO (Command) для Application слоя.
     */
    public function toCommand(): RegisterUserCommand
    {
        return new RegisterUserCommand(
            nickname: (string) $this->validated('nickname'),
            email: (string) $this->validated('email'),
            password: (string) $this->validated('password'),
            avatar: $this->file('avatar')
        );
    }
}
