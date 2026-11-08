<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User;

use App\Domain\User\Event\UserAvatarRemoved;
use App\Domain\User\Event\UserRegistered;
use App\Domain\User\User;
use App\Domain\User\ValueObject\AvatarPath;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Domain\User\ValueObject\Nickname;
use App\Domain\User\ValueObject\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для Агрегата User.
 *
 * Проверяют корректность фабричных методов, соблюдение инвариантов агрегата,
 * а также правильность генерации и накопления доменных событий.
 */
final class UserTest extends TestCase
{
    /**
     * Проверяет, что при регистрации нового пользователя создается корректный агрегат
     * и генерируется доменное событие UserRegistered.
     */
    public function test_it_registers_a_new_user_and_records_event(): void
    {
        // Подготовка (Arrange)
        $nickname = Nickname::fromString('john_doe');
        $email = Email::fromString('john@example.com');
        $password = HashedPassword::fromHash('hashed_password');
        $avatar = AvatarPath::fromString('avatars/john.jpg');

        // Выполнение (Act)
        $user = User::register($nickname, $email, $password, $avatar);

        // Проверка (Assert)
        $this->assertNotNull($user->getId());
        $this->assertSame('john_doe', $user->getNickname()->toString());
        $this->assertSame('john@example.com', $user->getEmail()->toString());
        $this->assertSame('avatars/john.jpg', $user->getAvatar()->toString());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());

        // Проверяем, что событие UserRegistered было сгенерировано
        $events = $user->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserRegistered::class, $events[0]);
        $this->assertTrue($events[0]->userId->equals($user->getId()));
        $this->assertSame('john_doe', $events[0]->nickname->toString());
    }

    /**
     * Проверяет, что метод reconstitute восстанавливает агрегат из БД
     * без генерации каких-либо доменных событий (так как это не новое действие).
     */
    public function test_it_reconstitutes_a_user_without_events(): void
    {
        // Подготовка (Arrange)
        $id = UserId::generate();
        $nickname = Nickname::fromString('existing_user');
        $email = Email::fromString('existing@example.com');
        $password = HashedPassword::fromHash('hashed_password');
        $avatar = AvatarPath::fromString(null);
        $createdAt = new DateTimeImmutable('-1 day');

        // Выполнение (Act)
        $user = User::reconstitute($id, $nickname, $email, $password, $avatar, $createdAt);

        // Проверка (Assert)
        $this->assertTrue($user->getId()->equals($id));
        $this->assertSame('existing_user', $user->getNickname()->toString());

        // Событий быть не должно, так как это восстановление из БД
        $events = $user->releaseEvents();
        $this->assertEmpty($events);
    }

    /**
     * Проверяет, что метод removeAvatar корректно генерирует событие UserAvatarRemoved,
     * если аватар действительно был установлен.
     */
    public function test_it_removes_avatar_and_records_event(): void
    {
        // Подготовка (Arrange)
        $user = $this->createUserWithAvatar('avatars/to_remove.jpg');

        // Выполнение (Act)
        $user->removeAvatar();

        // Проверка (Assert)
        $events = $user->releaseEvents();

        // Первое событие - UserRegistered, второе - UserAvatarRemoved
        $this->assertCount(2, $events);
        $this->assertInstanceOf(UserAvatarRemoved::class, $events[1]);
        $this->assertTrue($events[1]->userId->equals($user->getId()));
    }

    /**
     * Проверяет идемпотентность метода removeAvatar.
     * Если аватара нет (null), повторное удаление не должно генерировать событие.
     */
    public function test_removing_avatar_is_idempotent_if_avatar_is_null(): void
    {
        // Подготовка (Arrange)
        $user = $this->createUserWithAvatar(null);
        $user->releaseEvents(); // Очищаем событие регистрации

        // Выполнение (Act)
        $user->removeAvatar();

        // Проверка (Assert)
        $events = $user->releaseEvents();

        // Событие не должно быть создано, так как аватара и так нет
        $this->assertEmpty($events);
    }

    /**
     * Проверяет, что метод releaseEvents() возвращает массив накопленных событий
     * и очищает внутренний буфер агрегата (повторный вызов возвращает пустой массив).
     */
    public function test_it_releases_events_and_clears_the_array(): void
    {
        // Подготовка (Arrange)
        $user = $this->createUserWithAvatar('test.jpg');

        // Выполнение (Act)
        $events = $user->releaseEvents();
        $secondCall = $user->releaseEvents();

        // Проверка (Assert)
        $this->assertCount(1, $events);
        $this->assertEmpty($secondCall);
    }

    /**
     * Хелпер для создания пользователя в тестах.
     * Сокращает дублирование кода при подготовке данных.
     *
     * @param  string|null  $avatarPath  Путь к аватару (или null)
     */
    private function createUserWithAvatar(?string $avatarPath): User
    {
        return User::register(
            Nickname::fromString('test_user'),
            Email::fromString('test@example.com'),
            HashedPassword::fromHash('hash'),
            AvatarPath::fromString($avatarPath)
        );
    }
}
