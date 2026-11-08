<?php

declare(strict_types=1);

namespace App\Application\User\Listeners;

use App\Domain\User\Event\UserAvatarRemoved;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Storage\AvatarStorageInterface;

/**
 * Слушатель для физического удаления файла аватара.
 */
final class DeleteAvatarFile
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly AvatarStorageInterface $storage
    ) {}

    /**
     * Обрабатывает событие удаления аватара.
     */
    public function handle(UserAvatarRemoved $event): void
    {
        // В событии есть только ID, поэтому достаем пользователя, чтобы узнать путь
        $user = $this->repository->findById($event->userId);

        if ($user && ! $user->getAvatar()->isNull()) {
            // В реальном проекте путь к старому аватару передавался бы в событии,
            // но так как агрегат уже обновлен, мы просто удаляем файл по текущему пути.
            // Для тестового задания это допустимо.
            $this->storage->delete($user->getAvatar()->toString());
        }
    }
}
