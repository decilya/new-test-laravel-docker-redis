<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Domain\User\Service\PasswordHasherInterface;
use Illuminate\Support\Facades\Hash;

/**
 * Реализация сервиса хеширования паролей через фасад Hash.
 */
final class LaravelPasswordHasher implements PasswordHasherInterface
{
    /**
     * Хеширует пароль.
     */
    public function hash(string $plainPassword): string
    {
        return Hash::make($plainPassword);
    }
}
