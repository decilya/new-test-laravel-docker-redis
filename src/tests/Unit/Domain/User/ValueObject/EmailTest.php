<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\ValueObject;

use App\Domain\User\Exception\InvalidEmail;
use App\Domain\User\ValueObject\Email;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для Value Object Email.
 */
final class EmailTest extends TestCase
{
    public function test_it_can_be_created_with_valid_email(): void
    {
        $email = Email::fromString('John.Doe@Example.com');
        // Проверяем, что приводится к нижнему регистру
        $this->assertSame('john.doe@example.com', $email->toString());
    }

    public function test_it_throws_exception_if_invalid(): void
    {
        $this->expectException(InvalidEmail::class);
        Email::fromString('not-an-email');
    }
}
