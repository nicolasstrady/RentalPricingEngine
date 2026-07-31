COMPOSE = docker compose
PHP = $(COMPOSE) exec php
COMPOSER = $(PHP) composer
CONSOLE = $(PHP) php bin/console

.PHONY: up down build rebuild install shell logs console

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
