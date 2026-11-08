<?php

declare(strict_types=1);

namespace Tests\Unit\Application\User\Listeners;

use App\Application\User\Cache\UserListCacheInterface;
use App\Application\User\Listeners\InvalidateUserListCache;
use App\Domain\User\Event\UserAvatarRemoved;
use App\Domain\User\ValueObject\UserId;
use Mockery;
use Tests\TestCase;

final class InvalidateUserListCacheTest extends TestCase
{
    public function test_it_invalidates_user_list_cache_on_event(): void
    {
        // Подготовка (Arrange)
        $cache = Mockery::mock(UserListCacheInterface::class);
        $cache->shouldReceive('invalidate')->once();

        $listener = new InvalidateUserListCache($cache);
        $event = new UserAvatarRemoved(UserId::generate());

        // Выполнение (Act)
        $listener->handle($event);

        // Проверка (Assert)
        $this->assertTrue(true);
    }
}
