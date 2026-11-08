<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\User\Exception\InvalidNickname;

/**
 * Value Object: Никнейм пользователя.
 *
 * Инкапсулирует бизнес-правила (инварианты) для никнейма.
 * Объект является иммутабельным (readonly): после создания изменить значение нельзя,
 * можно только создать новый экземпляр.
 *
 * @immutable
 */
final readonly class Nickname
{
    /** @var int Минимальная длина никнейма */
    private const MIN_LENGTH = 3;

    /** @var int Максимальная длина никнейма */
    private const MAX_LENGTH = 32;

    /**
     * @param  string  $value  Валидное значение никнейма
     */
    private function __construct(
        private string $value
    ) {}

    /**
     * Создает объект Nickname, проверяя все бизнес-правила.
     *
     * @param  string  $value  Сырая строка из запроса
     *
     * @throws InvalidNickname Если строка не соответствует требованиям (длина, символы)
     */
    public static function fromString(string $value): self
    {
        $trimmed = trim($value);
        $length = mb_strlen($trimmed);

        if ($length < self::MIN_LENGTH) {
            throw InvalidNickname::tooShort(self::MIN_LENGTH);
        }

        if ($length > self::MAX_LENGTH) {
            throw InvalidNickname::tooLong(self::MAX_LENGTH);
        }

        if (! preg_match('/^[a-zA-Z0-9_]+$/', $trimmed)) {
            throw InvalidNickname::invalidFormat();
        }

        return new self($trimmed);
    }

    /**
     * Возвращает строковое значение никнейма.
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Сравнивает текущий никнейм с другим.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
