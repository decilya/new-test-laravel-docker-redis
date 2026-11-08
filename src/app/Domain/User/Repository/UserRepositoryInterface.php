<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\User;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\Nickname;
use App\Domain\User\ValueObject\UserId;
use DateTimeImmutable;

/**
 * Интерфейс репозитория пользователей.
 */
interface UserRepositoryInterface
{
    /**
     * Сохраняет агрегат пользователя в хранилище.
     *
     * @param User $user
     * @return void
     */
    public function save(User $user): void;

    /**
     * Ищет пользователя по его уникальному идентификатору.
     *
     * @param UserId $id
     * @return User|null
     */
    public function findById(UserId $id): ?User;

    /**
     * Ищет пользователя по никнейму.
     *
     * @param Nickname $nickname
     * @return User|null
     */
    public function findByNickname(Nickname $nickname): ?User;

    /**
     * Ищет пользователя по email.
     *
     * @param Email $email
     * @return User|null
     */
    public function findByEmail(Email $email): ?User;

    /**
     * Возвращает список пользователей с курсорной пагинацией.
     *
     * @param int $perPage
     * @param string|null $cursor
     * @return array{users: array<User>, next_cursor: string|null}
     */
    public function paginate(int $perPage, ?string $cursor = null): array;

    /**
     * Удаляет пользователей, созданных до указанной даты.
     *
     * @param DateTimeImmutable $date
     * @return int Количество удаленных записей
     */
    public function deleteOlderThan(DateTimeImmutable $date): int;
}
