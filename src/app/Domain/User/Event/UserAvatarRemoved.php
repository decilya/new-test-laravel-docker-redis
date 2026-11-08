<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\User\ValueObject\UserId;

/**
 * Доменное событие: Аватар пользователя удален.
 *
 * Используется для инвалидации кэша списка пользователей (чтобы там обновился avatar_url),
 * а также для удаления физического файла из хранилища (если это делается асинхронно).
 *
 * @immutable
 */
final readonly class UserAvatarRemoved
{
    /**
     * @param  UserId  $userId  Уникальный идентификатор пользователя
     */
    public function __construct(
        public UserId $userId
    ) {}
}
