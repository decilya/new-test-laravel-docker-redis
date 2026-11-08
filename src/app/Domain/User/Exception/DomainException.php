<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

/**
 * Базовое исключение для всех доменных ошибок (Domain Exceptions).
 *
 * В DDD доменные исключения сигнализируют о нарушении бизнес-инвариантов (правил).
 * Они не зависят от инфраструктуры (БД, HTTP) и являются частью предметной области.
 * Обработка этих исключений и маппинг в HTTP-коды (например, 422 или 409)
 * происходит на уровне Application/Http слоев.
 */
abstract class DomainException extends \RuntimeException {}
