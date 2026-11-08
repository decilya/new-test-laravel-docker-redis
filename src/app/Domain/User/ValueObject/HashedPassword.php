<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

/**
 * Value Object: Хешированный пароль.
 *
 * Мы не храним пароли в открытом виде. Хеширование происходит
 * в слое Application (через сервис), а Domain принимает уже готовый хеш,
 * чтобы не зависеть от инфраструктуры хеширования (Hash::make).
 *
 * @immutable
 */
final readonly class HashedPassword
{
    /**
     * @param  string  $value  Уже сгенерированный хеш пароля
     */
    private function __construct(
        private string $value
    ) {}

    /**
     * Создает объект из готового хеша.
     */
    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    /**
     * Возвращает хеш пароля.
     */
    public function toString(): string
    {
        return $this->value;
    }
}
