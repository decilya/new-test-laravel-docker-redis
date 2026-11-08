<?php

declare(strict_types=1);

namespace App\Application\User\GetUserList;

/**
 * DTO (Query): Параметры запроса списка пользователей.
 */
final readonly class GetUserListQuery
{
    public function __construct(
        public int $perPage = 20,
        public ?string $cursor = null
    ) {}
}
