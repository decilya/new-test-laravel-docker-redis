<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

/**
 * Исключение при нарушении правил валидации Nickname.
 *
 * Указывает на то, что переданная строка не соответствует
 * бизнес-требованиям к никнейму пользователя.
 */
final class InvalidNickname extends DomainException
{
    /**
     * Никнейм слишком короткий.
     *
     * @param  int  $min  Минимально допустимая длина
     */
    public static function tooShort(int $min): self
    {
        return new self("Nickname must be at least {$min} characters long.");
    }

    /**
     * Никнейм слишком длинный.
     *
     * @param  int  $max  Максимально допустимая длина
     */
    public static function tooLong(int $max): self
    {
        return new self("Nickname must not exceed {$max} characters.");
    }

    /**
     * Никнейм содержит недопустимые символы (разрешены только a-z, A-Z, 0-9, _).
     */
    public static function invalidFormat(): self
    {
        return new self('Nickname can only contain letters, numbers, and underscores.');
    }
}
