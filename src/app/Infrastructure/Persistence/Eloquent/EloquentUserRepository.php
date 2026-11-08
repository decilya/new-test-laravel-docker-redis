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
 * Реализация репозитория пользователей на базе Eloquent ORM.
 */
final class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * @param User $user
     * @return void
     */
    public function save(User $user): void
    {
        $model = UserModel::find($user->getId()->toString());

        $data = [
            'nickname' => $user->getNickname()->toString(),
            'email' => $user->getEmail()->toString(),
            'password' => $user->getPassword()->toString(),
            'avatar_path' => $user->getAvatar()->toString(),
        ];

        if ($model) {
            $model->update($data);
        } else {
            $data['id'] = $user->getId()->toString();
            $data['created_at'] = $user->getCreatedAt()->format('Y-m-d H:i:s');
            UserModel::create($data);
        }
    }

    /**
     * @param UserId $id
     * @return User|null
     */
    public function findById(UserId $id): ?User
    {
        $model = UserModel::find($id->toString());

        return $model ? $this->toDomain($model) : null;
    }

    /**
     * @param Nickname $nickname
     * @return User|null
     */
    public function findByNickname(Nickname $nickname): ?User
    {
        $model = UserModel::where('nickname', $nickname->toString())->first();

        return $model ? $this->toDomain($model) : null;
    }

    /**
     * @param Email $email
     * @return User|null
     */
    public function findByEmail(Email $email): ?User
    {
        $model = UserModel::where('email', $email->toString())->first();

        return $model ? $this->toDomain($model) : null;
    }

    /**
     * @param int $perPage
     * @param string|null $cursor
     * @return array{users: array<User>, next_cursor: string|null}
     */
    public function paginate(int $perPage, ?string $cursor = null): array
    {
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

    /**
     * @param DateTimeImmutable $date
     * @return int
     */
    public function deleteOlderThan(DateTimeImmutable $date): int
    {
        return UserModel::where('created_at', '<', $date->format('Y-m-d H:i:s'))->delete();
    }

    /**
     * @param UserModel $model
     * @return User
     */
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
