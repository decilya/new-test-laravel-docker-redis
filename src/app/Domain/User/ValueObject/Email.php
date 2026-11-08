<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\User\Exception\InvalidEmail;

/**
 * Value Object: Email пользователя.
 *
 * Гарантирует, что в системе не может существовать некорректного email.
 * Автоматически приводит значение к нижнему регистру (нормализация),
 * чтобы избежать создания дублей (например, John@mail.com и john@mail.com).
 *
 * @immutable
 */
final readonly class Email
{
    /**
     * @param  string  $value  Нормализованный (валидный) email
     */
    private function __construct(
        private string $value
    ) {}

    /**
     * Создает объект Email, валидируя и нормализуя входную строку.
     *
     * @param  string  $value  Сырая строка из запроса
     *
     * @throws InvalidEmail Если строка не является валидным email-адресом
     */
    public static function fromString(string $value): self
    {
        $email = mb_strtolower(trim($value));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmail::invalidFormat($email);
        }

        return new self($email);
    }

    /**
     * Возвращает строковое значение email.
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Сравнивает текущий email с другим.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
