<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\User\Repository\UserRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Джоба для очистки устаревших данных (из ТЗ).
 *
 * Удаляет пользователей, зарегистрированных более 60 минут назад.
 */
final class CleanupOldDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Выполняет джобу.
     *
     * @param UserRepositoryInterface $repository
     * @return void
     */
    public function handle(UserRepositoryInterface $repository): void
    {
        $cutoff = new DateTimeImmutable('-60 minutes');
        $deletedCount = $repository->deleteOlderThan($cutoff);

        Log::info("CleanupOldDataJob: Deleted {$deletedCount} old users.");
    }
}
