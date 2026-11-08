<?php

declare(strict_types=1);

namespace App\Application\User\Listeners;

use App\Application\User\Cache\UserListCacheInterface;
use App\Domain\User\Event\UserAvatarRemoved;
use App\Domain\User\Event\UserRegistered;

/**
 * Слушатель доменных событий для инвалидации кэша списка пользователей.
 */
final readonly class InvalidateUserListCache
{
    /**
     * @param UserListCacheInterface $cache
     */
    public function __construct(
        private UserListCacheInterface $cache
    ) {}

    /**
     * @param UserRegistered|UserAvatarRemoved $event
     * @return void
     */
    public function handle(UserRegistered|UserAvatarRemoved $event): void
    {
        $this->cache->invalidate();
    }
}
