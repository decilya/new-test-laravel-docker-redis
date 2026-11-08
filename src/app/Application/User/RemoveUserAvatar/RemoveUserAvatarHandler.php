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
final readonly class RemoveUserAvatarHandler
{
    /**
     * @param UserRepositoryInterface $repository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        private UserRepositoryInterface $repository,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * @param RemoveUserAvatarCommand $command
     * @return void
     */
    public function handle(RemoveUserAvatarCommand $command): void
    {
        $userId = UserId::fromString($command->userId);
        $user = $this->repository->findById($userId);

        if ($user === null) {
            throw new UserNotFound($command->userId);
        }

        $user->removeAvatar();
        $this->repository->save($user);

        $this->eventDispatcher->dispatch($user->releaseEvents());
    }
}
