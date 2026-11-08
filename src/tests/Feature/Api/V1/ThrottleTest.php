<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Тесты для проверки Rate Limiting (Throttle).
 */
final class ThrottleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_it_returns_429_after_too_many_requests(): void
    {
        // Переопределяем лимитер прямо в тесте на 2 запроса
        RateLimiter::for('api', function () {
            return Limit::perMinute(2);
        });

        // Делаем 2 успешных запроса
        $this->getJson('/api/v1/users');
        $this->getJson('/api/v1/users');

        // 3-й запрос должен вернуть 429 Too Many Requests
        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(429);
    }
}
