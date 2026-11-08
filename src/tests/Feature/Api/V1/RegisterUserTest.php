<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature-тесты для эндпоинта регистрации пользователя.
 *
 * Проверяют полный цикл: от HTTP-запроса до сохранения в базе данных.
 */
final class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверяет успешную регистрацию с аватаром.
     */
    public function test_it_registers_user_successfully(): void
    {
        // Подготовка (Arrange)
        Storage::fake('public');
        $avatar = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $payload = [
            'nickname' => 'john_doe',
            'email' => 'john@example.com',
            'password' => 'secure_password',
            'avatar' => $avatar,
        ];

        // Выполнение (Act)
        $response = $this->postJson('/api/v1/users', $payload);

        // Проверка (Assert)
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'nickname', 'avatar_url', 'created_at'],
            ]);

        // Проверяем, что файл сохранен
        Storage::disk('public')->assertExists('avatars/'.$avatar->hashName());

        // Проверяем, что пользователь есть в БД
        $this->assertDatabaseHas('users', [
            'nickname' => 'john_doe',
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Проверяет ошибку валидации (422) при неверных данных.
     */
    public function test_it_fails_validation_on_invalid_data(): void
    {
        // Подготовка (Arrange)
        $payload = [
            'nickname' => 'jo', // Слишком короткий
            'email' => 'not-an-email',
            'password' => '123',
        ];

        // Выполнение (Act)
        $response = $this->postJson('/api/v1/users', $payload);

        // Проверка (Assert)
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nickname', 'email', 'password']);
    }

    /**
     * Проверяет ошибку 409 (Conflict) при дубликате никнейма.
     */
    public function test_it_returns_409_if_nickname_taken(): void
    {
        // Подготовка (Arrange)
        $this->postJson('/api/v1/users', [
            'nickname' => 'john_doe',
            'email' => 'john@example.com',
            'password' => 'secure_password',
        ]);

        // Выполнение (Act)
        $response = $this->postJson('/api/v1/users', [
            'nickname' => 'john_doe', // Дубликат
            'email' => 'other@example.com',
            'password' => 'secure_password',
        ]);

        // Проверка (Assert)
        $response->assertStatus(409)
            ->assertJsonPath('error.code', 'NicknameAlreadyTaken');
    }
}
