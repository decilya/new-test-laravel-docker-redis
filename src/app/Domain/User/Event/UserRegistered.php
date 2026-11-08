<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\Nickname;
use App\Domain\User\ValueObject\UserId;

/**
 * Доменное событие: Пользователь зарегистрирован.
 *
 * Сигнализирует о том, что в системе появился новый пользователь.
 * Может быть использовано для отправки приветственного письма,
 * инвалидации кэша списка пользователей и других побочных эффектов.
 *
 * @immutable
 */
final readonly class UserRegistered
{
    /**
     * @param  UserId  $userId  Уникальный идентификатор нового пользователя
     * @param  Nickname  $nickname  Никнейм пользователя
     * @param  Email  $email  Email пользователя
     */
    public function __construct(
        public UserId $userId,
        public Nickname $nickname,
        public Email $email
    ) {}
}
