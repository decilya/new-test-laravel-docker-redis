<?php

declare(strict_types=1);

namespace App\Domain\User\Service;

/**
 * Интерфейс для хеширования паролей.
 *
 * Абстрагирует алгоритм хеширования (Bcrypt, Argon2) от бизнес-логики.
 * Реализация находится в слое Infrastructure (Laravel Hash).
 */
interface PasswordHasherInterface
{
    /**
     * Хеширует сырой пароль.
     *
     * @param  string  $plainPassword  Пароль в открытом виде
     * @return string Хеш пароля
     */
    public function hash(string $plainPassword): string;
}
