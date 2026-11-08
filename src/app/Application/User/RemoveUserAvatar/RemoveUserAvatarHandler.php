<?php

declare(strict_types=1);

namespace App\Application\User\RemoveUserAvatar;

use App\Application\Shared\EventDispatcherInterface;
use App\Domain\User\Exception\UserNotFound;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\UserId;

/**
 * Обработчик сценария: Удаление аватара пользователя.
 */
final class RemoveUserAvatarHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function handle(RemoveUserAvatarCommand $command): void
    {
        $userId = UserId::fromString($command->userId);
        $user = $this->repository->findById($userId);

        if ($user === null) {
            throw new UserNotFound($command->userId);
        }

        $user->removeAvatar();
        $this->repository->save($user);

        // Публикуем события
        $this->eventDispatcher->dispatch($user->releaseEvents());
    }
}
