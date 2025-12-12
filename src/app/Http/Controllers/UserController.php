<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Регистрация нового пользователя
     */
    public function register(UserRequest $request)
    {
        // Валидация уже выполнена в UserRequest
        $nickname = $request->input('nickname');


        /** @var User $user */
        $user = new User();
        $user->nickname = $nickname;
        $user->name = $nickname;
        $user->email = $nickname . '@test.test';
        $user->password = 'PASSWORD';

        // Сохраняем аватар
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;


        $user->save();

        Redis::set("user:nickname:{$nickname}", 'taken');

        // Счетчики онлайн событий - https://ru.hexlet.io/courses/redis-basics/lessons/counters/theory_unit
        Redis::incr('stats:registrations:total');
        Redis::zadd('stats:registrations:daily', time(), $user->id);

        return response()->json([
            'message' => 'Пользователь успешно зарегистрирован.',
            'user' => [
                'id' => $user->id,
                'nickname' => $user->nickname,
                'avatar_url' => asset('storage/' . $user->avatar)
            ]
        ], 201); // 201 - код состояния HTTP, который означает, что запрос выполнен успешно, и сервер создал новый ресурс.
        // Если в команде отдельно обговорено, то можно вернуть и просто 200
    }

    /**
     * Вывод списка всех пользователей (с кэшированием в Redis)
     */
    public function list()
    {
        $cacheKey = 'users:list:html';
        $ttl = 180; // Кешируем на 180 секунд

        // Пытаемся получить кэшированный HTML
        $cachedHtml = Redis::get($cacheKey);
        if ($cachedHtml) {
            return $cachedHtml;
        }

        // Если кэша нет — формируем HTML из БД
        $users = User::all();

        $html = '<h1>Зарегистрированные пользователи</h1><ul>';
        foreach ($users as $user) {
            $html .= '<li>';
            $html .= '<strong>' . e($user->nickname) . '</strong>: ';
            $html .= '<img src="' . asset('storage/' . $user->avatar) . '" width="50" height="50" alt="Аватар">';
            $html .= '</li>';
        }
        $html .= '</ul>';

        // Сохраняем в Redis
        Redis::setex($cacheKey, $ttl, $html);

        return $html;
    }
}
