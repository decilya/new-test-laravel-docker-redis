<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\User\GetUserList\GetUserListHandler;
use App\Application\User\GetUserList\GetUserListQuery;
use App\Application\User\RegisterUser\RegisterUserHandler;
use App\Application\User\RemoveUserAvatar\RemoveUserAvatarCommand;
use App\Application\User\RemoveUserAvatar\RemoveUserAvatarHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Контроллер для управления пользователями.
 */
final class UserController extends Controller
{
    public function store(RegisterUserRequest $request, RegisterUserHandler $handler): JsonResponse
    {
        $userView = $handler->handle($request->toCommand());

        return (new UserResource($userView))->response()->setStatusCode(201)
            ->header('Location', "/api/v1/users/{$userView->id}");
    }

    public function index(Request $request, GetUserListHandler $handler): AnonymousResourceCollection
    {
        // Защита от перегрузки: минимум 1, максимум 100 записей
        $perPage = min(max((int) $request->query('per_page', 20), 1), 100);
        $cursor = $request->query('cursor');

        $result = $handler->handle(new GetUserListQuery($perPage, $cursor));

        return UserResource::collection($result['users'])->additional([
            'next_cursor' => $result['next_cursor'],
        ]);
    }

    public function destroyAvatar(string $id, RemoveUserAvatarHandler $handler): Response
    {
        $handler->handle(new RemoveUserAvatarCommand($id));

        return response()->noContent();
    }
}
