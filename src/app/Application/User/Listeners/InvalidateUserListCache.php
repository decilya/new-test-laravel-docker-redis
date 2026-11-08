<?php

declare(strict_types=1);

namespace App\Application\User\Listeners;

use App\Domain\User\Event\UserAvatarRemoved;
use App\Domain\User\Event\UserRegistered;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Слушатель доменных событий для инвалидации кэша списка пользователей.
 *
 * Реагирует на регистрацию нового пользователя и удаление аватара.
 */
final class InvalidateUserListCache
{
    public function __construct(
        private readonly CacheRepository $cache
    ) {}

    /**
     * Обрабатывает событие.
     */
    public function handle(UserRegistered|UserAvatarRemoved $event): void
    {
        // В реальном проекте здесь использовались бы Cache::tags(['users'])->flush(),
        // но для простоты и поддержки array-драйвера в тестах мы очищаем весь кэш.
        $this->cache->flush();
    }
}
