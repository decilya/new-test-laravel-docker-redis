<?php

declare(strict_types=1);

namespace App\Application\User\Listeners;

use App\Application\User\Cache\UserListCacheInterface;
use App\Domain\User\Event\UserAvatarRemoved;
use App\Domain\User\Event\UserRegistered;

/**
 * Слушатель доменных событий для инвалидации кэша списка пользователей.
 */
final class InvalidateUserListCache
{
    public function __construct(
        private readonly UserListCacheInterface $cache
    ) {}

    public function handle(UserRegistered|UserAvatarRemoved $event): void
    {
        $this->cache->invalidate();
    }
}
