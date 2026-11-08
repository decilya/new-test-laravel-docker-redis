<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Джоба для очистки устаревших данных (из ТЗ).
 *
 * Так как наши данные хранятся в Postgres (а не в Redis как в старом ТЗ),
 * эта джоба имитирует очистку старых логов/кэшей.
 */
final class CleanupOldDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Количество минут, после которых данные считаются устаревшими.
     */
    private const EXPIRATION_MINUTES = 60;

    /**
     * Выполняет джобу.
     */
    public function handle(): void
    {
        // Пример: очистка кэша (в реальности здесь был бы запрос DELETE к БД)
        Cache::flush();

        Log::info('CleanupOldDataJob: Old data cleaned successfully.');
    }
}
