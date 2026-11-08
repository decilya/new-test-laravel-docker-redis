<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Application\User\Cache\UserListCacheInterface;
use App\Jobs\CleanupOldDataJob;
use Mockery;
use Tests\TestCase;

/**
 * Тесты для джобы очистки старых данных (CleanupOldDataJob).
 */
final class CleanupOldDataJobTest extends TestCase
{
    /**
     * Проверяет, что джоба корректно вызывает инвалидацию кэша списка пользователей.
     *
     * @return void
     */
    public function test_it_cleans_up_data(): void
    {
        $cacheMock = Mockery::mock(UserListCacheInterface::class);
        $cacheMock->shouldReceive('invalidate')->once();

        $job = new CleanupOldDataJob();
        $job->handle($cacheMock);

        $this->assertTrue(true);
    }
}
