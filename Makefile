.PHONY: all help erase build cache-folders composer-install composer-update composer-require composer-require-dev start stop logs bash grumphp fix-cs tests behat migrations consume-commands consume-events run-minikube stop-minikube

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
DOCKER_DB_SERVICE=db
DOCKER_DB_PORT=5432
DOCKER_AMQP_SERVICE=amqp
DOCKER_AMQP_PORT=5672

# DEFAULT COMMANDS -----------------------------------------------------------------------------------------------------
all: help

help: ## Display the available commands in this Makefile
	@echo "╔══════════════════════════════════════════════════════════════════════════════╗"
	@echo "║                           ${CYAN}.:${RESET} AVAILABLE COMMANDS ${CYAN}:.${RESET}                           ║"
	@echo "╚══════════════════════════════════════════════════════════════════════════════╝"
	@echo ""
	@grep -E '^[a-zA-Z_0-9%-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "${COMMAND_COLOR}%-40s${RESET} ${HELP_COLOR}%s${RESET}\n", $$1, $$2}'
	@echo ""

# BUILD COMMANDS -------------------------------------------------------------------------------------------------------
init: erase cache-folders build composer-install start migrations ## Initialize the project (clean, build, install, migrate)

erase: ## Remove Docker containers and volumes
		docker compose down -v

build: ## Build and pull Docker images
		docker compose build && \
		docker compose pull

cache-folders: ## Create and set permissions for cache folders
		mkdir -p ~/.composer && chown ${UID}:${GID} ~/.composer

composer-install: ## Install Composer dependencies
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} composer install --verbose

composer-update: ## Update Composer dependencies
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} composer update --verbose

composer-require: ## Require a Composer dependency
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} composer require --verbose

composer-require-dev: ## Require a development-only Composer dependency
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} composer require --dev --verbose

start: ## Start Docker services
		docker compose up -d

stop: ## Stop Docker services
		docker compose stop

logs: ## Display logs of the PHP service
		docker compose logs -f ${DOCKER_PHP_SERVICE}

bash: ## Access the PHP container shell
		docker compose exec -it -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} sh

grumphp: ## Run GrumPHP to enforce code quality
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} grumphp run

fix-cs: ## Automatically fix code style issues using PSR-12
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} phpcbf --standard=PSR12 src tests

tests: ## Run unit tests with PHPUnit
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} phpunit --configuration phpunit.xml.dist --coverage-text --order=random --colors=never

behat: ## Run acceptance tests with behat
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} behat --strict --stop-on-failure --format progress

migrations: ## Initialize environment and execute migrations
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} sh -lc 'while ! nc -z ${DOCKER_DB_SERVICE} ${DOCKER_DB_PORT}; do echo "Waiting for DB service"; sleep 3; done;'
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} sh -lc 'while ! nc -z ${DOCKER_AMQP_SERVICE} ${DOCKER_AMQP_PORT}; do echo "Waiting for AMQP service"; sleep 3; done;'
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} sh -lc './bin/console app:environment:init'

consume-commands: ## Consume messages from the commands transport
		docker compose exec -it -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} console messenger:consume commands --bus=messenger_command.bus --time-limit=60 --limit=30 --memory-limit=128M --no-interaction

consume-events: ## Consume messages from the events transport
		docker compose exec -it -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} console messenger:consume events --bus=messenger_event.bus --time-limit=60 --limit=30 --memory-limit=128M --no-interaction

run-minikube: ## Run using minikube
	minikube start --driver=docker
	minikube addons enable default-storageclass
	minikube addons enable storage-provisioner
	minikube addons enable ingress

	docker build -t your-repo/your-php-image:latest -f docker/php/Dockerfile .
	docker build -t your-repo/your-nginx-image:latest -f docker/nginx/Dockerfile .
	docker build -t your-repo/your-asyncapi-image:latest -f docker/asyncapi/Dockerfile .
	docker build -t your-repo/your-rabbimq-image:latest -f docker/rabbitmq/Dockerfile .

	minikube image load your-repo/your-php-image:latest
	minikube image load your-repo/your-nginx-image:latest
	minikube image load your-repo/your-asyncapi-image:latest
	minikube image load your-repo/your-rabbimq-image:latest

	kubectl config use-context minikube

	kubectl apply -f .deployment/namespace.yaml --context=minikube
	kubectl apply -f .deployment/configmap.yaml --context=minikube
	kubectl apply -f .deployment/ingress.yaml --context=minikube
	kubectl apply -f .deployment/postgres.yaml --context=minikube
	kubectl apply -f .deployment/rabbitmq.yaml --context=minikube
	kubectl apply -f .deployment/cronjob.yaml --context=minikube
	kubectl apply -f .deployment/workers.yaml --context=minikube
	kubectl apply -f .deployment/deployment.yaml --context=minikube
	kubectl apply -f .deployment/swagger.yaml --context=minikube
	kubectl apply -f .deployment/asyncapi.yaml --context=minikube
	kubectl apply -f .deployment/migration-job.yaml --context=minikube

	minikube ip

stop-minikube: ## Stop using minikube
	minikube stop
	minikube delete
