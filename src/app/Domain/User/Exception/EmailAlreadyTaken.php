<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

/**
 * Исключение: Email уже занят.
 */
final class EmailAlreadyTaken extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct("The email '{$email}' is already registered.");
    }
}
