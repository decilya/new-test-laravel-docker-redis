<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;
use App\Domain\User\ValueObject\AvatarPath;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Domain\User\ValueObject\Nickname;
use App\Domain\User\ValueObject\UserId;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use DateTimeImmutable;

/**
 * Реализация репозитория пользователей на базе Eloquent.
 */
final class EloquentUserRepository implements UserRepositoryInterface
{
    public function save(User $user): void
    {
        UserModel::updateOrCreate(
            ['id' => $user->getId()->toString()],
            [
                'nickname' => $user->getNickname()->toString(),
                'email' => $user->getEmail()->toString(),
                'password' => $user->getPassword()->toString(),
                'avatar_path' => $user->getAvatar()->toString(),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ]
        );
    }

    public function findById(UserId $id): ?User
    {
        $model = UserModel::find($id->toString());

        return $model ? $this->toDomain($model) : null;
    }

    public function findByNickname(Nickname $nickname): ?User
    {
        $model = UserModel::where('nickname', $nickname->toString())->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByEmail(Email $email): ?User
    {
        $model = UserModel::where('email', $email->toString())->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function paginate(int $perPage, ?string $cursor = null): array
    {
        // Используем встроенную курсорную пагинацию Laravel (по id)
        $paginator = UserModel::orderBy('id', 'desc')->cursorPaginate($perPage, ['*'], 'cursor', $cursor);

        $users = array_map(
            fn (UserModel $model) => $this->toDomain($model),
            $paginator->items()
        );

        return [
            'users' => $users,
            'next_cursor' => $paginator->nextCursor()?->encode(),
        ];
    }

    private function toDomain(UserModel $model): User
    {
        return User::reconstitute(
            UserId::fromString($model->id),
            Nickname::fromString($model->nickname),
            Email::fromString($model->email),
            HashedPassword::fromHash($model->password),
            AvatarPath::fromString($model->avatar_path),
            new DateTimeImmutable($model->created_at->toDateTimeString())
        );
    }
}
