<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

/**
 * Исключение при некорректном формате Email.
 *
 * Гарантирует, что в систему не попадут невалидные email-адреса.
 */
final class InvalidEmail extends DomainException
{
    /**
     * Email не прошел проверку FILTER_VALIDATE_EMAIL.
     *
     * @param  string  $email  Строка, которая не является валидным email
     */
    public static function invalidFormat(string $email): self
    {
        return new self("The email '{$email}' is not valid.");
    }
}
