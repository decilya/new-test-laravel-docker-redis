<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\CleanupOldDataJob;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Тесты для джобы очистки.
 */
final class CleanupOldDataJobTest extends TestCase
{
    /**
     * Проверяет, что джоба очищает кэш.
     */
    public function test_it_cleans_up_data(): void
    {
        // Подготовка (Arrange)
        Cache::put('old_key', 'value', 60);
        $this->assertNotNull(Cache::get('old_key'));

        // Выполнение (Act)
        (new CleanupOldDataJob)->handle();

        // Проверка (Assert)
        $this->assertNull(Cache::get('old_key'));
    }
}
