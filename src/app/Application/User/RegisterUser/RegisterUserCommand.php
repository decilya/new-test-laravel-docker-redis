<?php

declare(strict_types=1);

namespace App\Application\User\RegisterUser;

/**
 * DTO (Command): Данные для регистрации нового пользователя.
 *
 * Передает сырые данные из HTTP-слоя (FormRequest) в Application-слой.
 * Является иммутабельным объектом.
 */
final readonly class RegisterUserCommand
{
    /**
     * @param string $nickname
     * @param string $email
     * @param string $password Сырой пароль (будет захеширован)
     * @param mixed $avatar Загруженный файл (или null)
     */
    public function __construct(
        public string $nickname,
        public string $email,
        public string $password,
        public mixed $avatar = null
    ) {}
}
