<?php

declare(strict_types=1);

namespace App\Application\User\RegisterUser;

use App\Application\Shared\EventDispatcherInterface;
use App\Application\User\UserView;
use App\Domain\User\Exception\EmailAlreadyTaken;
use App\Domain\User\Exception\NicknameAlreadyTaken;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHasherInterface;
use App\Domain\User\Storage\AvatarStorageInterface;
use App\Domain\User\User;
use App\Domain\User\ValueObject\AvatarPath;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Domain\User\ValueObject\Nickname;

/**
 * Обработчик сценария: Регистрация пользователя.
 */
final class RegisterUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly PasswordHasherInterface $hasher,
        private readonly AvatarStorageInterface $avatarStorage,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function handle(RegisterUserCommand $command): UserView
    {
        $nickname = Nickname::fromString($command->nickname);
        $email = Email::fromString($command->email);

        if ($this->repository->findByNickname($nickname) !== null) {
            throw new NicknameAlreadyTaken($nickname->toString());
        }

        if ($this->repository->findByEmail($email) !== null) {
            throw new EmailAlreadyTaken($email->toString());
        }

        $avatarPath = AvatarPath::fromString(null);
        if ($command->avatar !== null) {
            $path = $this->avatarStorage->store($command->avatar);
            $avatarPath = AvatarPath::fromString($path);
        }

        $hashedPassword = HashedPassword::fromHash(
            $this->hasher->hash($command->password)
        );

        $user = User::register($nickname, $email, $hashedPassword, $avatarPath);
        $this->repository->save($user);

        // Публикуем доменные события (инвалидация кэша)
        $this->eventDispatcher->dispatch($user->releaseEvents());

        return UserView::fromAggregate($user);
    }
}
