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
 * Проверяют полный цикл от HTTP-запроса до сохранения в базе данных,
 * включая валидацию, обработку файлов и бизнес-исключений.
 */
final class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверяет успешную регистрацию пользователя с аватаром.
     *
     * Ожидает: статус 201, правильную структуру JSON, наличие файла на диске и запись в БД.
     *
     * @return void
     */
    public function test_it_registers_user_successfully(): void
    {
        Storage::fake('public');
        $avatar = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $payload = [
            'nickname' => 'john_doe',
            'email' => 'john@example.com',
            'password' => 'secure_password',
            'avatar' => $avatar,
        ];

        $response = $this->postJson('/api/v1/users', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'nickname', 'avatar_url', 'created_at'],
            ]);

        Storage::disk('public')->assertExists('avatars/'.$avatar->hashName());

        $this->assertDatabaseHas('users', [
            'nickname' => 'john_doe',
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Проверяет ошибку валидации (422) при передаче неверных данных.
     *
     * Ожидает: статус 422 и наличие ошибок для полей nickname, email, password, avatar.
     *
     * @return void
     */
    public function test_it_fails_validation_on_invalid_data(): void
    {
        $payload = [
            'nickname' => 'jo',
            'email' => 'not-an-email',
            'password' => '123',
        ];

        $response = $this->postJson('/api/v1/users', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nickname', 'email', 'password', 'avatar']);
    }

    /**
     * Проверяет ошибку 409 (Conflict) при попытке занять существующий никнейм.
     *
     * Ожидает: статус 409 и код ошибки 'NicknameAlreadyTaken'.
     *
     * @return void
     */
    public function test_it_returns_409_if_nickname_taken(): void
    {
        Storage::fake('public');

        $this->postJson('/api/v1/users', [
            'nickname' => 'john_doe',
            'email' => 'john@example.com',
            'password' => 'secure_password',
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response = $this->postJson('/api/v1/users', [
            'nickname' => 'john_doe',
            'email' => 'other@example.com',
            'password' => 'secure_password',
            'avatar' => UploadedFile::fake()->image('avatar2.jpg'),
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error.code', 'NicknameAlreadyTaken');
    }
}
