<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Domain\User\User;
use App\Domain\User\ValueObject\AvatarPath;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Domain\User\ValueObject\Nickname;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Feature-тесты для эндпоинтов списка и удаления аватара.
 */
final class UserListAndAvatarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Проверяет работу пагинации и кэширования.
     */
    public function test_it_returns_paginated_list(): void
    {
        // Подготовка (Arrange)
        for ($i = 1; $i <= 3; $i++) {
            UserModel::create([
                'id' => \Symfony\Component\Uid\Uuid::v7()->toRfc4122(),
                'nickname' => "user_{$i}",
                'email' => "user{$i}@example.com",
                'password' => 'hash',
            ]);
        }

        // Выполнение (Act)
        $response = $this->getJson('/api/v1/users?per_page=2');

        // Проверка (Assert)
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data', 'next_cursor']);

        // Проверяем, что запрос закэшировался
        $this->assertNotNull(Cache::get('users.list.'.md5('2|first')));
    }

    /**
     * Проверяет удаление аватара.
     */
    public function test_it_removes_avatar(): void
    {
        // Подготовка (Arrange)
        $user = User::register(
            Nickname::fromString('john_doe'),
            Email::fromString('john@example.com'),
            HashedPassword::fromHash('hash'),
            AvatarPath::fromString('avatars/pic.jpg')
        );
        app(\App\Domain\User\Repository\UserRepositoryInterface::class)->save($user);

        // Выполнение (Act)
        $response = $this->deleteJson("/api/v1/users/{$user->getId()->toString()}/avatar");

        // Проверка (Assert)
        $response->assertStatus(204);
        $this->assertDatabaseHas('users', [
            'id' => $user->getId()->toString(),
            'avatar_path' => null,
        ]);
    }
}
