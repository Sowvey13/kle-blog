.PHONY: up down build logs seed test pint

up:
	docker compose up -d --build

down:
	docker compose down

build:
	docker compose build

logs:
	docker compose logs -f

seed:
	docker compose exec backend php artisan migrate --force
	docker compose exec backend php artisan db:seed --force

test:
	docker compose exec backend php artisan test
	docker compose exec frontend php artisan test

pint:
	docker compose exec backend ./vendor/bin/pint --test
	docker compose exec frontend ./vendor/bin/pint --test
