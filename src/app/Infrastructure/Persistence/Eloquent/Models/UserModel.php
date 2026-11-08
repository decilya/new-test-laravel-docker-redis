<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent-модель для таблицы users.
 *
 * Является частью слоя Infrastructure. Отвечает только за маппинг
 * полей базы данных и не содержит бизнес-логики.
 *
 * @property string $id
 * @property string $nickname
 * @property string $email
 * @property string $password
 * @property string|null $avatar_path
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
final class UserModel extends Model
{
    // Трейт HasUuids в Laravel 12 по умолчанию генерирует UUIDv7
    use HasUuids;

    /**
     * @var string Имя таблицы
     */
    protected $table = 'users';

    /**
     * @var array<string> Поля, доступные для массового заполнения
     */
    protected $fillable = [
        'id',
        'nickname',
        'email',
        'password',
        'avatar_path',
        'created_at',
        'updated_at',
    ];
}
