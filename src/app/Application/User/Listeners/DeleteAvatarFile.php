<?php

declare(strict_types=1);

namespace App\Application\User\Listeners;

use App\Domain\User\Event\UserAvatarRemoved;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Storage\AvatarStorageInterface;

/**
 * Слушатель для физического удаления файла аватара.
 * 
 * Реагирует на доменное событие UserAvatarRemoved.
 */
final class DeleteAvatarFile
{
    /**
     * @param UserRepositoryInterface $repository Репозиторий для поиска пользователя.
     * @param AvatarStorageInterface $storage Сервис для удаления файлов.
     */
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly AvatarStorageInterface $storage
    ) {
    }

    /**
     * Обрабатывает событие удаления аватара.
     * 
     * @param UserAvatarRemoved $event Событие, содержащее ID пользователя.
     * @return void
     */
    public function handle(UserAvatarRemoved $event): void
    {
        $user = $this->repository->findById($event->userId);

        if ($user !== null && !$user->getAvatar()->isNull()) {
            $this->storage->delete($user->getAvatar()->toString());
        }
    }
}
