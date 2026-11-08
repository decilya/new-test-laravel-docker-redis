<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure;

use App\Domain\User\User;
use App\Domain\User\ValueObject\AvatarPath;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Domain\User\ValueObject\Nickname;
use App\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Интеграционные тесты для EloquentUserRepository.
 *
 * Проверяют реальное взаимодействие с базой данных PostgreSQL.
 * Используют трейт RefreshDatabase для очистки БД между тестами.
 */
final class EloquentUserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверяет сохранение и поиск пользователя по ID и никнейму.
     */
    public function test_it_saves_and_finds_user(): void
    {
        // Подготовка (Arrange)
        $repository = new EloquentUserRepository;

        $user = User::register(
            Nickname::fromString('john_doe'),
            Email::fromString('john@example.com'),
            HashedPassword::fromHash('hashed_pass'),
            AvatarPath::fromString('avatars/pic.jpg')
        );

        // Выполнение (Act)
        $repository->save($user);

        // Проверка (Assert)
        $foundById = $repository->findById($user->getId());
        $this->assertNotNull($foundById);
        $this->assertSame('john_doe', $foundById->getNickname()->toString());

        $foundByNickname = $repository->findByNickname(Nickname::fromString('john_doe'));
        $this->assertNotNull($foundByNickname);
        $this->assertTrue($user->getId()->equals($foundByNickname->getId()));
    }
}
