COMPOSE = docker compose
PHP = $(COMPOSE) exec php
COMPOSER = $(PHP) composer
CONSOLE = $(PHP) php bin/console

.PHONY: up down build rebuild install shell logs console database database-create migrate fixtures test-database test analyse cs-fix cs-check lint composer-validate quality

up:
	$(COMPOSE) up --detach

down:
	$(COMPOSE) down

build:
	$(COMPOSE) build

rebuild:
	$(COMPOSE) up --detach --build

install:
	$(COMPOSER) install

shell:
	$(PHP) sh

logs:
	$(COMPOSE) logs --follow

console:
	$(CONSOLE)

database: database-create migrate fixtures

database-create:
	$(CONSOLE) doctrine:database:create --if-not-exists

migrate:
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --allow-no-migration

fixtures:
	$(CONSOLE) foundry:load-fixtures --no-interaction

test-database:
	$(COMPOSER) test-database

test:
	$(COMPOSER) test

analyse:
	$(COMPOSER) analyse

cs-fix:
	$(COMPOSER) cs-fix

cs-check:
	$(COMPOSER) cs-check

lint: cs-check analyse

composer-validate:
	$(COMPOSER) validate --strict

quality: composer-validate lint test
