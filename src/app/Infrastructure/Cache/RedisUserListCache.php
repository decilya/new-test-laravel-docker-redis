<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use App\Application\User\Cache\UserListCacheInterface;
use Illuminate\Support\Facades\Cache;

/**
 * Реализация кэша списка пользователей на Redis.
 * Использует теги, чтобы не сбрасывать весь кэш приложения.
 */
final class RedisUserListCache implements UserListCacheInterface
{
    private const TAG = 'users:list';

    public function remember(string $key, int $ttl, callable $callback): array
    {
        return Cache::tags([self::TAG])->remember($key, $ttl, $callback);
    }

    public function invalidate(): void
    {
        // Очищаем только кэш с тегом users:list
        Cache::tags([self::TAG])->flush();
    }
}
