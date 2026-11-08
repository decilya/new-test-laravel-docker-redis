<?php

declare(strict_types=1);

namespace App\Application\User\GetUserList;

use App\Application\User\Cache\UserListCacheInterface;
use App\Application\User\UserView;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;

/**
 * Обработчик сценария: Получение списка пользователей с кэшированием.
 */
final class GetUserListHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly UserListCacheInterface $cache
    ) {}

    public function handle(GetUserListQuery $query): array
    {
        $cacheKey = 'users.list.'.md5($query->perPage.'|'.($query->cursor ?? 'first'));

        $result = $this->cache->remember($cacheKey, 60, function () use ($query) {
            return $this->repository->paginate($query->perPage, $query->cursor);
        });

        $userViews = array_map(
            fn (User $user) => UserView::fromAggregate($user),
            $result['users']
        );

        return [
            'users' => $userViews,
            'next_cursor' => $result['next_cursor'],
        ];
    }
}
