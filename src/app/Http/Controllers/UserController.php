<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Регистрация нового пользователя
     */
    public function register(UserRequest $request)
    {
        // Валидация уже выполнена в UserRequest

        /** @var User $user */
        $user = new User();
        $user->nickname = $request->input('nickname');
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = $request->input('password');

        // Если нужно хэшировать пароль перед сохранением
        $user->password = Hash::make($request->input('password'));

        // Сохраняем аватар
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;

        try {
            $user->save();
        } catch (\Throwable $e){
            Log::error($e->getMessage());
            die($e->getMessage());
        }

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
     * еще смотри метод newList()
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

    /**
     * Вывод списка всех пользователей (с кэшированием в Redis) в представление
     */
    public function newList()
    {
        $page = (int)request('page', 1);

        $cacheKey = 'cache.users.list.page.' . $page;
        $cacheTTL = 180;

        return Cache::remember(
            $cacheKey,
            $cacheTTL,
            function () use ($page) {
                // Оптимизируем запрос: выбираем только нужные поля
                $users = User::select([
                    'id',
                    'nickname',
                    'avatar',
                ])->paginate(25);

                // Преобразуем пути к аватарам в URL для Blade-шаблона
                $users->each(function ($user) {
                    if ($user->avatar) {
                        $user->avatar_url = asset('storage/avatars/' . $user->avatar);
                    } else {
                        // Задаём URL дефолтного аватара, если пользователь не загрузил свой
                        $user->avatar_url = asset('images/default-avatar.jpeg');
                    }
                });

                if ($users->isEmpty()) {
                    return view('users.list', ['users' => $users])->render();
                }

                return view('users.list', compact('users'))->render();
            }
        );
    }


    /**
     * @param UserRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(UserRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        // Передаём запрос в метод register для обработки
        $response = $this->register($request);

        // Проверяем, был ли запрос успешным (код 201)
        if ($response->getStatusCode() === 201) {
            return redirect()->route('users.list')
                ->with('success', 'Пользователь успешно добавлен!');
        }

        return $response;
    }


    public function create()
    {
        return view('users.form', ['user' => null]);
    }


    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    /**
     * @param UserRequest $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserRequest $request, User $user)
    {
        $validatedData = $request->validated();

        $user->nickname = $validatedData['nickname'];
        $user->name = $validatedData['nickname'];

        if ($request->hasFile('avatar')) {
            // Удаляем старый аватар, если есть
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return redirect()->route('users.list')
            ->with('success', 'Пользователь успешно обновлён!');
    }

    /**
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeAvatar(User $user)
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }

        return redirect()->route('users.edit', $user)
            ->with('success', 'Аватар удалён!');
    }


}
