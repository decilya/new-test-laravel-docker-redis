<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

/**
 * Исключение: Никнейм уже занят.
 */
final class NicknameAlreadyTaken extends DomainException
{
    public function __construct(string $nickname)
    {
        parent::__construct("The nickname '{$nickname}' is already taken.");
    }
}
