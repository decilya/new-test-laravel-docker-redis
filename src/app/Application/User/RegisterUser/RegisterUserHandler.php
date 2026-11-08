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
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Обработчик сценария: Регистрация пользователя.
 */
final readonly class RegisterUserHandler
{
    /**
     * @param UserRepositoryInterface $repository
     * @param PasswordHasherInterface $hasher
     * @param AvatarStorageInterface $avatarStorage
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        private UserRepositoryInterface $repository,
        private PasswordHasherInterface $hasher,
        private AvatarStorageInterface $avatarStorage,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * @param RegisterUserCommand $command
     * @return UserView
     */
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

        // Аватар теперь обязателен, сохраняем его напрямую
        $path = $this->avatarStorage->store($command->avatar);
        $avatarPath = AvatarPath::fromString($path);

        $hashedPassword = HashedPassword::fromHash(
            $this->hasher->hash($command->password)
        );

        $user = User::register($nickname, $email, $hashedPassword, $avatarPath);

        try {
            $this->repository->save($user);
        } catch (UniqueConstraintViolationException $e) {
            if (str_contains($e->getMessage(), 'nickname')) {
                throw new NicknameAlreadyTaken($nickname->toString());
            }
            if (str_contains($e->getMessage(), 'email')) {
                throw new EmailAlreadyTaken($email->toString());
            }
            throw $e;
        }

        $this->eventDispatcher->dispatch($user->releaseEvents());

        return UserView::fromAggregate($user);
    }
}
