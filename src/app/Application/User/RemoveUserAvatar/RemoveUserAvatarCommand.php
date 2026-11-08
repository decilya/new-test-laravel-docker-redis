<?php

declare(strict_types=1);

namespace App\Application\User\RemoveUserAvatar;

/**
 * DTO (Command): Команда на удаление аватара.
 */
final readonly class RemoveUserAvatarCommand
{
    public function __construct(
        public string $userId
    ) {}
}
