<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Shared\EventDispatcherInterface;
use App\Application\User\Listeners\InvalidateUserListCache;
use App\Domain\User\Event\UserAvatarRemoved;
use App\Domain\User\Event\UserRegistered;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHasherInterface;
use App\Domain\User\Storage\AvatarStorageInterface;
use App\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use App\Infrastructure\Services\LaravelEventDispatcher;
use App\Infrastructure\Services\LaravelPasswordHasher;
use App\Infrastructure\Storage\LocalAvatarStorage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Провайдер для связывания интерфейсов с реализациями и регистрации событий.
 */
final class AppServiceProvider extends ServiceProvider
{
    /**
     * Регистрация биндингов в контейнере.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
        $this->app->bind(AvatarStorageInterface::class, LocalAvatarStorage::class);
        $this->app->bind(EventDispatcherInterface::class, LaravelEventDispatcher::class);
    }

    /**
     * Настройка событий и планировщика.
     */
    public function boot(): void
    {
        // Привязываем слушателя к доменным событиям
        Event::listen(UserRegistered::class, InvalidateUserListCache::class);
        Event::listen(UserAvatarRemoved::class, InvalidateUserListCache::class);
    }
}
