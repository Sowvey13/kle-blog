COMPOSE := docker compose
BACKEND := $(COMPOSE) run --rm --no-deps backend
FRONTEND := $(COMPOSE) run --rm --no-deps frontend

.PHONY: install fresh-install up down build logs seed test pint pint-check

.env:
	@awk -v app="$$(openssl rand -hex 24)" -v root="$$(openssl rand -hex 24)" \
		'/^MYSQL_PASSWORD=$$/ { $$0 = "MYSQL_PASSWORD=" app } /^MYSQL_ROOT_PASSWORD=$$/ { $$0 = "MYSQL_ROOT_PASSWORD=" root } { print }' \
		.env.example > .env
	@echo ".env oluşturuldu, MySQL parolaları rastgele üretildi."

install: .env
	$(BACKEND) composer install --no-interaction --prefer-dist
	$(BACKEND) prepare
	$(FRONTEND) composer install --no-interaction --prefer-dist
	$(FRONTEND) npm ci
	$(FRONTEND) npm run build
	$(FRONTEND) prepare

fresh-install: .env build install
	$(COMPOSE) run --rm backend php artisan migrate:fresh --seed --force
	$(COMPOSE) up -d

up: .env
	$(COMPOSE) up -d --build

down:
	$(COMPOSE) down

build: .env
	$(COMPOSE) build

logs:
	$(COMPOSE) logs -f

seed: .env
	$(COMPOSE) run --rm backend php artisan migrate --force
	$(COMPOSE) run --rm backend php artisan db:seed --force

test: .env
	$(BACKEND) php artisan test
	$(FRONTEND) php artisan test

pint: .env
	$(BACKEND) vendor/bin/pint
	$(FRONTEND) vendor/bin/pint

pint-check: .env
	$(BACKEND) vendor/bin/pint --test
	$(FRONTEND) vendor/bin/pint --test
