<?php

declare(strict_types=1);

namespace Tests\Unit\Application\User\RegisterUser;

use App\Application\Shared\EventDispatcherInterface;
use App\Application\User\RegisterUser\RegisterUserCommand;
use App\Application\User\RegisterUser\RegisterUserHandler;
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RegisterUserHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $repository;

    private PasswordHasherInterface&MockObject $hasher;

    private AvatarStorageInterface&MockObject $avatarStorage;

    private EventDispatcherInterface&MockObject $dispatcher;

    private RegisterUserHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(UserRepositoryInterface::class);
        $this->hasher = $this->createMock(PasswordHasherInterface::class);
        $this->avatarStorage = $this->createMock(AvatarStorageInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->handler = new RegisterUserHandler(
            $this->repository,
            $this->hasher,
            $this->avatarStorage,
            $this->dispatcher
        );
    }

    public function test_it_registers_user_successfully_without_avatar(): void
    {
        $command = new RegisterUserCommand('john_doe', 'john@example.com', 'pass', null);

        $this->repository->method('findByNickname')->willReturn(null);
        $this->repository->method('findByEmail')->willReturn(null);
        $this->hasher->method('hash')->willReturn('hashed');
        $this->repository->expects($this->once())->method('save');
        $this->dispatcher->expects($this->once())->method('dispatch');

        $result = $this->handler->handle($command);

        $this->assertSame('john_doe', $result->nickname);
    }

    public function test_it_throws_exception_if_nickname_taken(): void
    {
        $command = new RegisterUserCommand('john_doe', 'john@example.com', 'pass', null);

        $existingUser = User::register(
            Nickname::fromString('john_doe'),
            Email::fromString('other@example.com'),
            HashedPassword::fromHash('hash'),
            AvatarPath::fromString(null)
        );

        $this->repository->method('findByNickname')->willReturn($existingUser);
        $this->expectException(NicknameAlreadyTaken::class);

        $this->handler->handle($command);
    }

    public function test_it_throws_exception_if_email_taken(): void
    {
        $command = new RegisterUserCommand('john_doe', 'john@example.com', 'pass', null);

        $existingUser = User::register(
            Nickname::fromString('other_user'),
            Email::fromString('john@example.com'),
            HashedPassword::fromHash('hash'),
            AvatarPath::fromString(null)
        );

        $this->repository->method('findByNickname')->willReturn(null);
        $this->repository->method('findByEmail')->willReturn($existingUser);
        $this->expectException(EmailAlreadyTaken::class);

        $this->handler->handle($command);
    }

    public function test_it_throws_exception_if_nickname_taken_in_db_race_condition(): void
    {
        $command = new RegisterUserCommand('john_doe', 'john@example.com', 'pass', null);

        $this->repository->method('findByNickname')->willReturn(null);
        $this->repository->method('findByEmail')->willReturn(null);
        $this->hasher->method('hash')->willReturn('hashed');

        // Правильный мок исключения БД для Laravel 12
        $exception = new UniqueConstraintViolationException(
            'pgsql',
            'insert into users...',
            [],
            new \PDOException('SQLSTATE[23505]: Unique violation: 7 ERROR: duplicate key value violates unique constraint "users_nickname_unique"')
        );

        $this->repository->expects($this->once())
            ->method('save')
            ->willThrowException($exception);

        $this->expectException(NicknameAlreadyTaken::class);

        $this->handler->handle($command);
    }
}
