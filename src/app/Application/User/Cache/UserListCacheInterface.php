<?php

declare(strict_types=1);

namespace App\Application\User\Cache;

/**
 * Интерфейс кэша для списка пользователей.
 *
 * Позволяет изолировать логику кэширования от общего кэша приложения
 * (чтобы не сбрасывать rate-limiter).
 */
interface UserListCacheInterface
{
    /**
     * Запоминает результат в кэше.
     *
     * @param string $key
     * @param int $ttl
     * @param callable $callback
     * @return array
     */
    public function remember(string $key, int $ttl, callable $callback): array;

    /**
     * Инвалидирует весь кэш списка пользователей.
     *
     * @return void
     */
    public function invalidate(): void;
}
