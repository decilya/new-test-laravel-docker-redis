# Тестовое задание

Тестовое задание (Backend, Laravel)
1. Развернуть проект на последнем Laravel.
2. Собрать приложение в Docker
3. Развернуть Redis рядом, в который будут сохраняться все данные.
4. Реализовать API
   4.1. POST /api/register
   Принимает в JSON: nickname и avatar
   Проверить на уникальность nickname, если уже есть — вернуть
   ошибку.
   Ограничить avatar по mime и по макс.размеру
   Ограничить по RPS — N запросов в минуту
   4.2 GET api/list
   Отобразить список всех зарегистрированных записей (примитивный
   html (можно даже без стилей), где будет nickname + avatar)
5. Создать job для очистки устаревших данных (старше X минут) и поставить
   его в запуск по расписанию каждых N минут
   Дополнительные плюсы
   Покрытие тестами API
   README
   Применить сode-style / code-quality инструменты

После скачивания проекта запустите: 

```bash
$ make install
```

Миграции:
```bash
$ docker compose exec app php artisan migrage
```

Какие смотреть файлы:

код:
- https://github.com/decilya/new-test-laravel-docker-redis/blob/main/src/database/migrations/0001_01_01_000000_create_users_table.php
- https://github.com/decilya/new-test-laravel-docker-redis/blob/main/src/routes/web.php
- https://github.com/decilya/new-test-laravel-docker-redis/blob/main/src/app/Http/Controllers/UserController.php
- https://github.com/decilya/new-test-laravel-docker-redis/blob/main/src/app/Http/Requests/UserRequest.php
- https://github.com/decilya/new-test-laravel-docker-redis/blob/main/src/app/Jobs/CleanOldRedisData.php
- https://github.com/decilya/new-test-laravel-docker-redis/blob/main/src/app/Console/Kernel.php

### тесты:
- https://github.com/decilya/new-test-laravel-docker-redis/blob/main/src/tests/Feature/RegisterTest.php
- https://github.com/decilya/new-test-laravel-docker-redis/blob/main/src/tests/Feature/ListTest.php

```bash
docker compose exec app php artisan test
```

### Качество кода:
Инструменты code-style и code-quality
```bash
composer require --dev phpstan/phpstan
composer require --dev laravel/pint

vendor/bin/phpstan analyse
php artisan pint
```

