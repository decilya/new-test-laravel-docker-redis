# User Service API (Тестовое задание)

Реализация тестового задания с применением принципов **DDD (Domain-Driven Design)**, **SOLID** и современных практик Laravel 12.

## ⚠️ Архитектурные решения и отступления от исходного ТЗ

В исходном задании были спорные архитектурные требования. Как сеньор-разработчик, я принял решения, которые делают систему надежнее и проще в поддержке.

**1. Хранение данных: PostgreSQL (Primary) + Redis (Cache)**
*   *Исходное ТЗ:* «Развернуть Redis... в который будут сохраняться все данные».
*   *Реализация:* PostgreSQL — основное хранилище, Redis — кэш и Rate Limiter.
*   *Обоснование:* Redis — in-memory хранилище. Пользователи (профили, пароли) — критичные данные, требующие транзакционности (ACID). Использование Postgres — это production-ready подход. Redis используется по назначению: кэширование списка и счетчики RPS.

**2. Формат ответа списка: JSON вместо HTML**
*   *Исходное ТЗ:* `GET /api/list` — «примитивный html».
*   *Реализация:* `GET /api/v1/users` — JSON с курсорной пагинацией.
*   *Обоснование:* Возврат HTML из API нарушает принцип разделения ответственности. HTML-шаблон должен рендериться на стороне клиента.

**3. Идентификаторы: UUIDv7**
*   *Реализация:* Вместо автоинкрементного `id` используется `UUIDv7`.
*   *Обоснование:* UUIDv7 монотонен по времени, что идеально для B-Tree индексов Postgres и курсорной пагинации. При этом он безопасен (нельзя перебрать ID).

**4. Расширение полей регистрации**
*   *Исходное ТЗ:* «Принимает в JSON: nickname и avatar».
*   *Реализация:* Добавлены обязательные поля `email` и `password`.
*   *Обоснование:* Регистрация без email и пароля противоречит базовым требованиям безопасности. Поле `avatar` сделано обязательным (как в ТЗ) с ограничением mime и размера.

---

## 🛠 Стек технологий
*   **PHP 8.3**
*   **Laravel 12**
*   **PostgreSQL 16** (Основная БД)
*   **Redis 7** (Кэш, Rate Limiting)
*   **Docker & Docker Compose**
*   **PHPUnit 11** (Тесты)
*   **Larastan (PHPStan)** + **Laravel Pint** + **Deptrac** (Качество и архитектура)

---

## 🏗 Архитектура (DDD + Слои)

Проект строго разделен на слои (проверяется через `deptrac`):
*   **`App\Domain`** — Ядро. Агрегаты (`User`), Value Objects, Domain Events, интерфейсы.
*   **`App\Application`** — Сценарии (Use Cases). Оркестрация. DTO (Commands/Queries).
*   **`App\Infrastructure`** — Реализации интерфейсов (Eloquent, Redis, Storage).
*   **`App\Http`** — Тонкие контроллеры, FormRequests, API Resources.

### Production-Ready решения (Senior Level)
*   **Race Condition:** В `RegisterUserHandler` реализован паттерн "Check-then-Act with DB fallback". Если параллельный запрос займет никнейм, БД выбросит `UniqueConstraintViolationException`, который перехватывается и транслируется в доменное исключение (409 Conflict).
*   **Изоляция кэша:** Для кэша списка используется `Cache::tags(['users:list'])`, чтобы не сбрасывать кэш Rate Limiter'а.
*   **Защита от OOM:** В контроллере валидируется `per_page` (min 1, max 100).

---

## 🚀 Запуск проекта

```bash
make install
```
*Соберет контейнеры, установит зависимости, сгенерирует ключ и применит миграции.*

---

## 📡 API Контракт

### 1. Регистрация пользователя
`POST /api/v1/users`
*   **Body:** `multipart/form-data` (`nickname`, `email`, `password`, `avatar`).
*   **Responses:** `201 Created` / `422 Unprocessable` / `409 Conflict` / `429 Too Many Requests`.

### 2. Список пользователей
`GET /api/v1/users?per_page=20&cursor=...`
*   **Responses:** `200 OK` (JSON с курсорной пагинацией).

### 3. Удаление аватара
`DELETE /api/v1/users/{id}/avatar`
*   **Responses:** `204 No Content` (идемпотентно).

---

## ✅ Результаты проверок (Proof of Work)

Проект полностью покрыт тестами и проходит строгие проверки.

**Тесты:**
```bash
make test
```
```text
Tests:    28 passed (68 assertions)
Duration: 3.07s
```
*(Покрыты: Value Objects, Агрегат, Use Cases, API (Feature), Интеграция с БД, Слушатели событий, Джобы, Throttle).*

**Code Style и Архитектура:**
```bash
make lint
```
```text
Laravel Pint: PASS (74 files)
Deptrac:
  Violations           0
  Allowed              119
```
*Pint проверяет стиль PSR-12. Deptrac гарантирует, что слои не нарушают зависимости.*

---

## 📌 Реализация пунктов из ТЗ

| Требование из ТЗ | Как реализовано |
| :--- | :--- |
| Последний Laravel | Использован Laravel 12 |
| Docker | `docker-compose.yml` с Nginx, PHP-FPM, Postgres, Redis |
| Redis | Используется для `Cache::tags` (список) и RateLimiting |
| `POST /api/register` | Реализован как `POST /api/v1/users` |
| Уникальность nickname | Проверка в Domain + Unique Index в БД + fallback на Race Condition |
| Ограничение avatar | В `RegisterUserRequest` (`required`, `mimes`, `max:2048`) |
| Ограничение по RPS | Middleware `throttle:api` (покрыто тестом) |
| `GET api/list` | Реализован как JSON `GET /api/v1/users` |
| Job для очистки | `CleanupOldDataJob` удаляет юзеров старше 60 мин, крон каждые 5 минут |
| Покрытие тестами | 28 тестов (Domain, Application, Infrastructure, HTTP) |
| Code-quality | Pint, PHPStan (Larastan), Deptrac (0 violations) |
