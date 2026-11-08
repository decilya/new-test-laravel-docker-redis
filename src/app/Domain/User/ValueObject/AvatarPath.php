<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

/**
 * Value Object: Путь к файлу аватара.
 *
 * Может быть null, если аватар не загружен (или был удален).
 * Хранит относительный путь к файлу в файловой системе (например, 'avatars/uuid.jpg').
 *
 * @immutable
 */
final readonly class AvatarPath
{
    /**
     * @param  string|null  $value  Путь к файлу или null
     */
    private function __construct(
        private ?string $value
    ) {}

    /**
     * Создает объект AvatarPath. Если передана пустая строка, приводится к null.
     */
    public static function fromString(?string $path): self
    {
        if ($path === null || trim($path) === '') {
            return new self(null);
        }

        return new self($path);
    }

    /**
     * Возвращает путь к аватару или null.
     */
    public function toString(): ?string
    {
        return $this->value;
    }

    /**
     * Проверяет, отсутствует ли аватар.
     */
    public function isNull(): bool
    {
        return $this->value === null;
    }
}
