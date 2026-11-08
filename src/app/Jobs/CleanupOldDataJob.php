<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Application\User\Cache\UserListCacheInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Джоба для очистки устаревших данных (из ТЗ).
 *
 * Инвалидирует кэш списка пользователей, не затрагивая другие кэши (например, Rate Limiter).
 */
final class CleanupOldDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Выполняет джобу.
     */
    public function handle(UserListCacheInterface $cache): void
    {
        // Инвалидируем ТОЛЬКО кэш списка, не трогая Rate Limiter
        $cache->invalidate();

        Log::info('CleanupOldDataJob: User list cache cleaned successfully.');
    }
}
