<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\User\Event\UserAvatarRemoved;
use App\Domain\User\Event\UserRegistered;
use App\Domain\User\ValueObject\AvatarPath;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Domain\User\ValueObject\Nickname;
use App\Domain\User\ValueObject\UserId;
use DateTimeImmutable;

/**
 * Агрегат: Пользователь (User Aggregate Root).
 */
final class User
{
    /**
     * @var array<object> Буфер накопленных доменных событий
     */
    private array $domainEvents = [];

    /**
     * Приватный конструктор.
     */
    private function __construct(
        private readonly UserId $id,
        private readonly Nickname $nickname,
        private readonly Email $email,
        private readonly HashedPassword $password,
        private AvatarPath $avatar, // Убрали readonly, так как аватар можно удалить
        private readonly DateTimeImmutable $createdAt
    ) {}

    /**
     * Фабричный метод для регистрации.
     */
    public static function register(
        Nickname $nickname,
        Email $email,
        HashedPassword $password,
        AvatarPath $avatar
    ): self {
        $id = UserId::generate();
        $createdAt = new DateTimeImmutable;

        $user = new self($id, $nickname, $email, $password, $avatar, $createdAt);
        $user->recordEvent(new UserRegistered($id, $nickname, $email));

        return $user;
    }

    /**
     * Восстановление из БД.
     */
    public static function reconstitute(
        UserId $id,
        Nickname $nickname,
        Email $email,
        HashedPassword $password,
        AvatarPath $avatar,
        DateTimeImmutable $createdAt
    ): self {
        return new self($id, $nickname, $email, $password, $avatar, $createdAt);
    }

    /**
     * Удаляет аватар пользователя.
     */
    public function removeAvatar(): void
    {
        if ($this->avatar->isNull()) {
            return; // Идемпотентность
        }

        // Меняем состояние агрегата
        $this->avatar = AvatarPath::fromString(null);

        // Генерируем событие
        $this->recordEvent(new UserAvatarRemoved($this->id));
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getNickname(): Nickname
    {
        return $this->nickname;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): HashedPassword
    {
        return $this->password;
    }

    public function getAvatar(): AvatarPath
    {
        return $this->avatar;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
