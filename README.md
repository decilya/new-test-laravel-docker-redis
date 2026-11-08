# User Service API (Тестовое задание)

Реализация тестового задания с применением принципов **DDD (Domain-Driven Design)**, **SOLID** и современных практик Laravel 12.

## ⚠️ Архитектурные решения и отступления от исходного ТЗ

В исходном задании были спорные архитектурные требования (хранение всех данных в Redis и возврат HTML из API). Как сеньор-разработчик, я принял решения, которые делают систему надежнее и проще в поддержке.

**1. Хранение данных: PostgreSQL (Primary) + Redis (Cache)**
*   *Исходное ТЗ:* «Развернуть Redis... в который будут сохраняться все данные».
*   *Реализация:* PostgreSQL — основное хранилище, Redis — кэш и Rate Limiter.
*   *Обоснование:* Redis — in-memory хранилище. Пользователи (профили, пароли) — критичные данные, требующие транзакционности (ACID). Использование Postgres — это production-ready подход. Redis используется по назначению: кэширование списка и счетчики RPS.

**2. Формат ответа списка: JSON вместо HTML**
*   *Исходное ТЗ:* `GET /api/list` — «примитивный html».
*   *Реализация:* `GET /api/v1/users` — JSON с курсорной пагинацией.
*   *Обоснование:* Возврат HTML из API нарушает принцип разделения ответственности. HTML-шаблон должен рендериться на стороне клиента (Frontend).

**3. Идентификаторы: UUIDv7**
*   *Реализация:* Вместо автоинкрементного `id` используется `UUIDv7`.
*   *Обоснование:* UUIDv7 монотонен по времени, что идеально для B-Tree индексов Postgres и курсорной пагинации. При этом он безопасен (нельзя перебрать ID) и позволяет генерировать ID в слое Domain *до* сохранения в БД, что критично для доменных событий.

---

## 🛠 Стек технологий
*   **PHP 8.3**
*   **Laravel 12**
*   **PostgreSQL 16** (Основная БД)
*   **Redis 7** (Кэш, Rate Limiting, очередь)
*   **Docker & Docker Compose**
*   **PHPUnit 11** (Тесты)
*   **Larastan (PHPStan)** + **Laravel Pint** + **Deptrac** (Качество и архитектура)

---

## 🏗 Архитектура (DDD + Слои)

Проект строго разделен на слои (проверяется через `deptrac`):
*   **`App\Domain`** — Ядро. Агрегаты (`User`), Value Objects (`Nickname`, `Email`), Domain Events, интерфейсы репозиториев. Не зависит от Laravel.
*   **`App\Application`** — Сценарии использования (Use Cases / Handlers). Оркестрация домена и инфраструктуры. DTO (Commands/Queries). Не зависит от Eloquent.
*   **`App\Infrastructure`** — Реализации интерфейсов (Eloquent, Redis, Storage).
*   **`App\Http`** — Тонкие контроллеры, FormRequests, API Resources. Только прием запроса и отдача ответа.

---

## 🚀 Запуск проекта

Для запуска достаточно выполнить одну команду (требуется установленный Docker):

```bash
make install
```
*Эта команда соберет контейнеры, установит зависимости, сгенерирует ключ и применит миграции.*

---

## 📡 API Контракт

### 1. Регистрация пользователя
`POST /api/v1/users`
*   **Body:** `multipart/form-data`
    *   `nickname` (string, unique, 3-32 символа)
    *   `email` (string, unique, valid email)
    *   `password` (string, min 8)
    *   `avatar` (file, jpeg/png, max 2MB)
*   **Responses:** `201 Created` / `422 Unprocessable` / `409 Conflict` / `429 Too Many Requests`.

### 2. Список пользователей
`GET /api/v1/users?per_page=20&cursor=...`
*   **Responses:** `200 OK` (JSON с курсорной пагинацией).

### 3. Удаление аватара
`DELETE /api/v1/users/{id}/avatar`
*   **Responses:** `204 No Content` (идемпотентно).

---

## ✅ Результаты проверок (Proof of Work)

Проект полностью покрыт тестами и проходит строгие проверки архитектуры и стиля кода.

**Запуск тестов:**
```bash
make test
```
```text
Tests:    26 passed (64 assertions)
Duration: 3.12s
```
*(Покрыты: Value Objects, Агрегат, Use Cases, API (Feature), Интеграция с БД, Слушатели событий, Джобы).*

**Проверка Code Style и Архитектуры:**
```bash
make lint
```
```text
Laravel Pint: PASS (81 files)
Deptrac:
  Violations           0
  Allowed              121
```
*Pint проверяет стиль PSR-12. Deptrac гарантирует, что слои не нарушают зависимости (например, Domain не импортирует Laravel).*

---

## 📌 Реализация пунктов из ТЗ

| Требование из ТЗ | Как реализовано |
| :--- | :--- |
| Последний Laravel | Использован Laravel 12 |
| Docker | `docker-compose.yml` с Nginx, PHP-FPM, Postgres, Redis |
| Redis | Используется для `Cache::remember` (список) и RateLimiting |
| `POST /api/register` | Реализован как `POST /api/v1/users` |
| Уникальность nickname | Проверка в Domain + Unique Index в БД |
| Ограничение avatar | В `RegisterUserRequest` (`mimes:jpeg,png`, `max:2048`) |
| Ограничение по RPS | Middleware `throttle:60,1` на группе роутов |
| `GET api/list` | Реализован как JSON `GET /api/v1/users` |
| Job для очистки | Реализована `CleanupOldDataJob`, запускается по крону каждые 5 минут |
| Покрытие тестами | 26 тестов (Domain, Application, Infrastructure, HTTP) |
| Code-quality | Pint, PHPStan (Larastan), Deptrac (0 violations) |
