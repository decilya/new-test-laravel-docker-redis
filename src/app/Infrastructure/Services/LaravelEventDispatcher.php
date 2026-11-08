<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\Shared\EventDispatcherInterface;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Реализация диспетчера событий через Laravel Event Dispatcher.
 */
final class LaravelEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher
    ) {}

    /**
     * Публикует события.
     *
     * @param  array<object>  $events
     */
    public function dispatch(array $events): void
    {
        foreach ($events as $event) {
            $this->dispatcher->dispatch($event);
        }
    }
}
