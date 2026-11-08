<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

/**
 * Исключение при передаче некорректного идентификатора пользователя.
 *
 * Используется для защиты от подмены типа идентификатора или
 * использования неправильной версии UUID (в нашем случае разрешен только v7).
 */
final class InvalidUserId extends DomainException
{
    /**
     * Строка не является валидным UUID.
     *
     * @param  string  $id  Некорректная строка
     */
    public static function invalidFormat(string $id): self
    {
        return new self("Invalid UUID format: '{$id}'.");
    }

    /**
     * UUID валиден, но его версия не поддерживается (ожидается только v7).
     *
     * @param  string  $id  Строка с неподдерживаемой версией UUID
     */
    public static function invalidVersion(string $id): self
    {
        return new self("Only UUIDv7 is allowed, got something else: '{$id}'.");
    }
}
