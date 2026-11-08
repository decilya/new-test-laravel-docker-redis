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
     */
    public function test_it_cleans_up_data(): void
    {
        // Подготовка (Arrange)
        // Создаем мок интерфейса кэша
        $cacheMock = Mockery::mock(UserListCacheInterface::class);

        // Ожидаем, что метод invalidate будет вызван ровно один раз
        $cacheMock->shouldReceive('invalidate')->once();

        // Выполнение (Act)
        $job = new CleanupOldDataJob;
        $job->handle($cacheMock);

        // Проверка (Assert)
        // Если Mockery не выбросил исключение, значит метод был вызван корректно
        $this->assertTrue(true);
    }
}
