<?php

declare(strict_types=1);

namespace Tests\Unit\Application\User\Listeners;

use App\Application\User\Listeners\InvalidateUserListCache;
use App\Domain\User\Event\UserAvatarRemoved;
use App\Domain\User\ValueObject\UserId;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Mockery;
use Tests\TestCase;

/**
 * Тесты для слушателя инвалидации кэша.
 */
final class InvalidateUserListCacheTest extends TestCase
{
    /**
     * Проверяет, что при событии кэш очищается.
     */
    public function test_it_flushes_cache_on_event(): void
    {
        // Подготовка (Arrange)
        $cache = Mockery::mock(CacheRepository::class);
        $cache->shouldReceive('flush')->once(); // Ожидаем, что flush вызовется 1 раз

        $listener = new InvalidateUserListCache($cache);
        $event = new UserAvatarRemoved(UserId::generate());

        // Выполнение (Act)
        $listener->handle($event);

        // Проверка (Assert)
        $this->assertTrue(true); // Если Mockery не выдал ошибку, тест пройден
    }
}
