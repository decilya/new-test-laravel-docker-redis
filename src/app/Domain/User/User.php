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
     *
     * @param Nickname $nickname
     * @param Email $email
     * @param HashedPassword $password
     * @param AvatarPath $avatar
     * @return self
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
     *
     * @param UserId $id
     * @param Nickname $nickname
     * @param Email $email
     * @param HashedPassword $password
     * @param AvatarPath $avatar
     * @param DateTimeImmutable $createdAt
     * @return self
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
     *
     * @return void
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

    /**
     * @return UserId
     */
    public function getId(): UserId
    {
        return $this->id;
    }

    /**
     * @return Nickname
     */
    public function getNickname(): Nickname
    {
        return $this->nickname;
    }

    /**
     * @return Email
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * @return HashedPassword
     */
    public function getPassword(): HashedPassword
    {
        return $this->password;
    }

    /**
     * @return AvatarPath
     */
    public function getAvatar(): AvatarPath
    {
        return $this->avatar;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param object $event
     * @return void
     */
    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * @return object[]
     */
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
