<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\ValueObject;

use App\Domain\User\Exception\InvalidNickname;
use App\Domain\User\ValueObject\Nickname;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для Value Object Nickname.
 * Проверяют граничные значения и соблюдение инвариантов.
 */
final class NicknameTest extends TestCase
{
    public function test_it_can_be_created_with_valid_string(): void
    {
        $nickname = Nickname::fromString('john_doe_99');
        $this->assertSame('john_doe_99', $nickname->toString());
    }

    public function test_it_throws_exception_if_too_short(): void
    {
        $this->expectException(InvalidNickname::class);
        Nickname::fromString('ab');
    }

    public function test_it_throws_exception_if_too_long(): void
    {
        $this->expectException(InvalidNickname::class);
        Nickname::fromString(str_repeat('a', 33));
    }

    public function test_it_throws_exception_on_invalid_chars(): void
    {
        $this->expectException(InvalidNickname::class);
        Nickname::fromString('john-doe!');
    }

    public function test_it_compares_equality(): void
    {
        $nick1 = Nickname::fromString('user_1');
        $nick2 = Nickname::fromString('user_1');
        $nick3 = Nickname::fromString('user_2');

        $this->assertTrue($nick1->equals($nick2));
        $this->assertFalse($nick1->equals($nick3));
    }
}
