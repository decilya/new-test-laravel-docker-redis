<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\User\Exception\InvalidUserId;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

/**
 * Value Object: Идентификатор пользователя.
 *
 * В DDD идентификатор агрегата (Entity) должен генерироваться в момент создания объекта,
 * еще до сохранения в базу данных. Использование UUIDv7 решает эту проблему,
 * так как он не требует участия БД (в отличие от auto-increment).
 *
 * UUIDv7 монотонен по времени, что обеспечивает высокую производительность
 * при вставке в B-Tree индексы PostgreSQL и идеально подходит для курсорной пагинации.
 *
 * @immutable
 */
final readonly class UserId
{
    /**
     * @param  UuidV7  $value  Внутреннее представление UUIDv7
     */
    private function __construct(
        private UuidV7 $value
    ) {}

    /**
     * Создает новый уникальный идентификатор.
     */
    public static function generate(): self
    {
        return new self(Uuid::v7());
    }

    /**
     * Восстанавливает идентификатор из строки (например, при чтении из БД или из HTTP-запроса).
     *
     * @param  string  $id  Строковое представление UUID
     *
     * @throws InvalidUserId Если строка не является UUID или имеет неверную версию (не v7)
     */
    public static function fromString(string $id): self
    {
        try {
            $uuid = Uuid::fromString($id);

            if (! $uuid instanceof UuidV7) {
                throw InvalidUserId::invalidVersion($id);
            }

            return new self($uuid);
        } catch (\InvalidArgumentException) {
            throw InvalidUserId::invalidFormat($id);
        }
    }

    /**
     * Возвращает строковое представление идентификатора (RFC 4122).
     */
    public function toString(): string
    {
        return $this->value->toRfc4122();
    }

    /**
     * Сравнивает текущий идентификатор с другим.
     *
     * @param  self  $other  Другой идентификатор для сравнения
     * @return bool True, если идентификаторы равны
     */
    public function equals(self $other): bool
    {
        return $this->value->equals($other->value);
    }
}
