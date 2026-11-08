<?php

declare(strict_types=1);

namespace App\Application\Shared;

/**
 * Интерфейс для публикации доменных событий.
 *
 * Позволяет слою Application публиковать события, не завися от Laravel Event Dispatcher.
 */
interface EventDispatcherInterface
{
    /**
     * Публикует массив доменных событий.
     *
     * @param  array<object>  $events
     */
    public function dispatch(array $events): void;
}
