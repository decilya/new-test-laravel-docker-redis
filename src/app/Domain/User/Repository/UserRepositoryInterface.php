<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\User;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\Nickname;
use App\Domain\User\ValueObject\UserId;

/**
 * Интерфейс репозитория пользователей.
 */
interface UserRepositoryInterface
{
    /**
     * Сохраняет агрегат пользователя в хранилище.
     */
    public function save(User $user): void;

    /**
     * Ищет пользователя по его уникальному идентификатору.
     */
    public function findById(UserId $id): ?User;

    /**
     * Ищет пользователя по никнейму (для проверки уникальности).
     */
    public function findByNickname(Nickname $nickname): ?User;

    /**
     * Ищет пользователя по email (для проверки уникальности).
     */
    public function findByEmail(Email $email): ?User;

    /**
     * Возвращает список пользователей с курсорной пагинацией.
     *
     * @param  int  $perPage  Количество записей на страницу
     * @param  string|null  $cursor  Курсор для продолжения пагинации
     * @return array{users: array<User>, next_cursor: string|null}
     */
    public function paginate(int $perPage, ?string $cursor = null): array;
}
