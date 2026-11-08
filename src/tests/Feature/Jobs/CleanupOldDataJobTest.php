<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Jobs\CleanupOldDataJob;
use DateTimeImmutable;
use Mockery;
use Tests\TestCase;

/**
 * Тесты для джобы очистки старых данных (CleanupOldDataJob).
 */
final class CleanupOldDataJobTest extends TestCase
{
    /**
     * Проверяет, что джоба вызывает метод удаления в репозитории с правильной датой.
     *
     * @return void
     */
    public function test_it_deletes_users_older_than_60_minutes(): void
    {
        $repositoryMock = Mockery::mock(UserRepositoryInterface::class);

        $repositoryMock->shouldReceive('deleteOlderThan')
            ->once()
            ->with(Mockery::on(function (DateTimeImmutable $date) {
                $expected = new DateTimeImmutable('-60 minutes');

                return abs($date->getTimestamp() - $expected->getTimestamp()) < 5;
            }))
            ->andReturn(5);

        $job = new CleanupOldDataJob();
        $job->handle($repositoryMock);

        $this->assertTrue(true);
    }
}
