<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\ValueObject;

use App\Domain\User\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для Value Object UserId.
 */
final class UserIdTest extends TestCase
{
    public function test_it_generates_uuid_v7(): void
    {
        $id = UserId::generate();

        // Проверяем, что это UUIDv7 (седьмой символ с конца в 3-й группе - '7')
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $id->toString()
        );
    }

    public function test_it_can_be_restored_from_string(): void
    {
        $original = UserId::generate();
        $restored = UserId::fromString($original->toString());

        $this->assertTrue($original->equals($restored));
    }
}
