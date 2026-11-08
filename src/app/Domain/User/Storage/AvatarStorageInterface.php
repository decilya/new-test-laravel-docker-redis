<?php

declare(strict_types=1);

namespace App\Domain\User\Storage;

/**
 * Интерфейс для работы с файловым хранилищем аватаров.
 *
 * Абстрагирует файловую систему (Local, S3, etc.) от бизнес-логики.
 */
interface AvatarStorageInterface
{
    /**
     * Сохраняет загруженный файл и возвращает путь к нему.
     *
     * @param  mixed  $file  Загруженный файл (в реализации Laravel это UploadedFile)
     * @return string Относительный путь к сохраненному файлу
     */
    public function store(mixed $file): string;
}
