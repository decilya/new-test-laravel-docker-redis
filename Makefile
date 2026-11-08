.RECIPEPREFIX = >
.PHONY: install test lint

install:
> docker compose up -d --build
> docker compose exec app composer install
> docker compose exec app cp -n .env.example .env || true
> docker compose exec app php artisan key:generate
> docker compose exec app php artisan migrate --seed

test:
> docker compose exec app php artisan test

lint:
> docker compose exec app ./vendor/bin/pint --test
> docker compose exec app ./vendor/bin/deptrac analyse --config-file=deptrac.yaml
