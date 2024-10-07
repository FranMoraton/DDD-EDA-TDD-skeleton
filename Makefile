.PHONY: all

# CONFIG ---------------------------------------------------------------------------------------------------------------
ifneq (,$(findstring xterm,${TERM}))
    BLACK   := $(shell tput -Txterm setaf 0)
    RED     := $(shell tput -Txterm setaf 1)
    GREEN   := $(shell tput -Txterm setaf 2)
    YELLOW  := $(shell tput -Txterm setaf 3)
    BLUE    := $(shell tput -Txterm setaf 4)
    MAGENTA := $(shell tput -Txterm setaf 5)
    CYAN    := $(shell tput -Txterm setaf 6)
    WHITE   := $(shell tput -Txterm setaf 7)
    RESET   := $(shell tput -Txterm sgr0)
else
    BLACK   := ""
    RED     := ""
    GREEN   := ""
    YELLOW  := ""
    BLUE    := ""
    MAGENTA := ""
    CYAN    := ""
    WHITE   := ""
    RESET   := ""
endif

COMMAND_COLOR := $(GREEN)
HELP_COLOR := $(BLUE)

UID=$(shell id -u)
GID=$(shell id -g)
DOCKER_PHP_SERVICE=php

# DEFAULT COMMANDS -----------------------------------------------------------------------------------------------------
all: help

help: ## Listar comandos disponibles en este Makefile
	@echo "╔══════════════════════════════════════════════════════════════════════════════╗"
	@echo "║                           ${CYAN}.:${RESET} AVAILABLE COMMANDS ${CYAN}:.${RESET}                           ║"
	@echo "╚══════════════════════════════════════════════════════════════════════════════╝"
	@echo ""
	@grep -E '^[a-zA-Z_0-9%-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "${COMMAND_COLOR}%-40s${RESET} ${HELP_COLOR}%s${RESET}\n", $$1, $$2}'
	@echo ""


# BUILD COMMANDS -------------------------------------------------------------------------------------------------------
init: erase cache-folders build composer-install start

erase:
		docker compose down -v

build:
		docker compose build --no-cache && \
		docker compose pull

cache-folders:
		mkdir -p ~/.composer && chown ${UID}:${GID} ~/.composer

composer-install:
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} composer install --verbose

composer-update:
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} composer update --verbose

composer-require:
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} composer require --verbose

composer-require-dev:
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} composer require --dev --verbose

start:
		docker compose up -d

stop:
		docker compose stop

bash:
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} sh

logs:
		docker compose logs -f ${DOCKER_PHP_SERVICE}

grumphp:
		docker compose exec ${DOCKER_PHP_SERVICE} grumphp run
