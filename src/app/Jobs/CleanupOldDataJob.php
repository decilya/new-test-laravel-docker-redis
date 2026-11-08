<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
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
     * @return void
     */
    public function handle(): void
    {
        // Удаляем пользователей старше 60 минут
        $deletedCount = UserModel::where('created_at', '<', now()->subMinutes(60))->delete();

        Log::info("CleanupOldDataJob: Deleted {$deletedCount} old users.");
    }
}
