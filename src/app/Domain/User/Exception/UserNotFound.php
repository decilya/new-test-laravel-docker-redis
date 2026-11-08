<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

/**
 * Исключение: Пользователь не найден.
 */
final class UserNotFound extends DomainException
{
    public function __construct(string $id)
    {
        parent::__construct("User with ID '{$id}' not found.");
    }
}
