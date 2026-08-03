COMPOSE = docker compose
PHP = $(COMPOSE) exec php
COMPOSER = $(PHP) composer
CONSOLE = $(PHP) php bin/console
FRONTEND = $(COMPOSE) exec frontend

.PHONY: up down build rebuild install backend-install frontend-install shell frontend-shell logs console database database-drop database-create migrate fixtures fixtures-append test-database test frontend-check frontend-test frontend-build coverage analyse cs-fix cs-check lint composer-validate quality

up:
	$(COMPOSE) up --build

down:
	$(COMPOSE) down

build:
	$(COMPOSE) build

rebuild:
	$(COMPOSE) up --detach --build

install: backend-install frontend-install

backend-install:
	$(COMPOSER) install

frontend-install:
	$(FRONTEND) npm ci

shell:
	$(PHP) sh

frontend-shell:
	$(FRONTEND) sh

logs:
	$(COMPOSE) logs --follow

console:
	$(CONSOLE)

database: database-drop database-create migrate fixtures-append

database-drop:
	$(CONSOLE) doctrine:database:drop --force --if-exists

database-create:
	$(CONSOLE) doctrine:database:create --if-not-exists

migrate:
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --allow-no-migration

fixtures:
	$(CONSOLE) foundry:load-fixtures --no-interaction

fixtures-append:
	$(CONSOLE) foundry:load-fixtures --no-interaction --append

test-database:
	$(COMPOSER) test-database

test:
	$(COMPOSER) test

frontend-test:
	$(FRONTEND) npm run test:ci

frontend-check:
	$(FRONTEND) npm run format:check

frontend-build:
	$(FRONTEND) npm run build

coverage: test-database
	$(COMPOSE) exec -e XDEBUG_MODE=coverage php php bin/phpunit --coverage-text --coverage-html var/coverage

analyse:
	$(COMPOSER) analyse

cs-fix:
	$(COMPOSER) cs-fix

cs-check:
	$(COMPOSER) cs-check

lint: cs-check analyse

composer-validate:
	$(COMPOSER) validate --strict

quality: composer-validate lint test frontend-check frontend-test frontend-build
