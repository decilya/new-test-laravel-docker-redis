<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Jobs\CleanupOldDataJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты для джобы очистки старых данных.
 */
final class CleanupOldDataJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверяет, что джоба удаляет старых пользователей и оставляет новых.
     *
     * @return void
     */
    public function test_it_deletes_users_older_than_60_minutes(): void
    {
        // Создаем старого пользователя (2 часа назад)
        $oldUser = UserModel::create([
            'id' => \Symfony\Component\Uid\Uuid::v7()->toRfc4122(),
            'nickname' => 'old_user',
            'email' => 'old@example.com',
            'password' => 'hash',
        ]);
        $oldUser->created_at = now()->subHours(2);
        $oldUser->save();

        // Создаем нового пользователя (только что)
        $newUser = UserModel::create([
            'id' => \Symfony\Component\Uid\Uuid::v7()->toRfc4122(),
            'nickname' => 'new_user',
            'email' => 'new@example.com',
            'password' => 'hash',
        ]);

        // Выполняем джобу
        (new CleanupOldDataJob())->handle();

        // Проверяем: старого нет, новый на месте
        $this->assertDatabaseMissing('users', ['id' => $oldUser->id]);
        $this->assertDatabaseHas('users', ['id' => $newUser->id]);
    }
}
