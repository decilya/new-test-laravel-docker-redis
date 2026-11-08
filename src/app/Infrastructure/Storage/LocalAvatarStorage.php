<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Domain\User\Storage\AvatarStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Реализация файлового хранилища для аватаров (локальный диск public).
 */
final class LocalAvatarStorage implements AvatarStorageInterface
{
    /**
     * Сохраняет файл и возвращает относительный путь.
     *
     * @throws \InvalidArgumentException Если передан не UploadedFile
     */
    public function store(mixed $file): string
    {
        if (! $file instanceof UploadedFile) {
            throw new \InvalidArgumentException('Expected instance of UploadedFile.');
        }

        return $file->store('avatars', 'public');
    }

    /**
     * Удаляет файл по указанному пути.
     */
    public function delete(string $path): void
    {
        Storage::disk('public')->delete($path);
    }
}
