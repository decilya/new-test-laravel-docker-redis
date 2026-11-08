<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\User;

/**
 * DTO (View): Представление пользователя для внешнего мира.
 *
 * Используется для возврата данных из Application-слоя в HTTP-слой.
 * Скрывает внутреннюю структуру Агрегата User и не позволяет
 * изменять его состояние извне.
 */
final readonly class UserView
{
    public function __construct(
        public string $id,
        public string $nickname,
        public ?string $avatarUrl,
        public string $createdAt
    ) {}

    /**
     * Фабричный метод для создания View из Агрегата.
     */
    public static function fromAggregate(User $user): self
    {
        return new self(
            $user->getId()->toString(),
            $user->getNickname()->toString(),
            $user->getAvatar()->toString(),
            $user->getCreatedAt()->format(\DATE_ATOM)
        );
    }
}
