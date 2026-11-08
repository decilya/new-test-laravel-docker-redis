<?php

declare(strict_types=1);

namespace App\Application\User\GetUserList;

use App\Application\User\UserView;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Обработчик сценария: Получение списка пользователей с кэшированием.
 */
final class GetUserListHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly CacheRepository $cache
    ) {}

    /**
     * Выполняет сценарий.
     *
     * @return array{users: array<UserView>, next_cursor: string|null}
     */
    public function handle(GetUserListQuery $query): array
    {
        $cacheKey = 'users.list.'.md5($query->perPage.'|'.($query->cursor ?? 'first'));

        $result = $this->cache->remember($cacheKey, 60, function () use ($query) {
            return $this->repository->paginate($query->perPage, $query->cursor);
        });

        // Маппим Агрегаты в DTO (UserView) для HTTP-слоя
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
