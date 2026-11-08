<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Shared\EventDispatcherInterface;
use App\Application\User\Cache\UserListCacheInterface;
use App\Application\User\Listeners\DeleteAvatarFile;
use App\Application\User\Listeners\InvalidateUserListCache;
use App\Domain\User\Event\UserAvatarRemoved;
use App\Domain\User\Event\UserRegistered;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHasherInterface;
use App\Domain\User\Storage\AvatarStorageInterface;
use App\Infrastructure\Cache\RedisUserListCache;
use App\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use App\Infrastructure\Services\LaravelEventDispatcher;
use App\Infrastructure\Services\LaravelPasswordHasher;
use App\Infrastructure\Storage\LocalAvatarStorage;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * Провайдер для связывания интерфейсов с реализациями и регистрации событий.
 */
final class AppServiceProvider extends ServiceProvider
{
    /**
     * Регистрация биндингов в контейнере зависимостей.
     *
     * Связывает доменные интерфейсы с их инфраструктурными реализациями.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
        $this->app->bind(AvatarStorageInterface::class, LocalAvatarStorage::class);
        $this->app->bind(EventDispatcherInterface::class, LaravelEventDispatcher::class);
        $this->app->bind(UserListCacheInterface::class, RedisUserListCache::class);
    }

    /**
     * Настройка событий и Rate Limiter.
     *
     * Регистрирует слушателей доменных событий и конфигурирует именованный
     * лимитер 'api' для контроля частоты запросов (в тестах лимит 5, в проде 60).
     *
     * @return void
     */
    public function boot(): void
    {
        // Слушатели событий
        Event::listen(UserRegistered::class, InvalidateUserListCache::class);
        Event::listen(UserAvatarRemoved::class, InvalidateUserListCache::class);
        Event::listen(UserAvatarRemoved::class, DeleteAvatarFile::class);

        // Именованный лимитер для API
        RateLimiter::for('api', function (Request $request) {
            $limit = app()->environment('testing') ? 5 : 60;

            return Limit::perMinute($limit)->by($request->ip());
        });
    }
}
