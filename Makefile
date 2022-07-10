
server_path := .server
env_file := $(server_path)/.env

docker_compose := docker-compose --project-directory $(server_path)

include $(env_file)
export $(shell sed 's/=.*//' $(env_file))

# HELP
# This will output the help for each task
# thanks to https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
.PHONY: help

help: ## This help.
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.DEFAULT_GOAL := help

build: ## Build the docker containers
	$(docker_compose) build

start: ## Start the docker containers
	$(docker_compose) up -d

stop: ## Stop the docker containers
	$(docker_compose) stop

restart: stop start ## Restart the docker containers

down: ## Stop and remove the docker containers
	$(docker_compose) down

login: ## Login to the running php container
	$(docker_compose) exec php sh --login

install: ## Install Composer dependencies
	$(docker_compose) run --rm -e XDEBUG_MODE=off -e COMPOSER_MEMORY_LIMIT=-1 php composer install

test: ## Run tests
	$(docker_compose) run --rm -e XDEBUG_MODE=off -e COMPOSER_MEMORY_LIMIT=-1 php composer run-script --timeout=0 test

fix: ## Run PHP Code Beautifier and Fixer
	$(docker_compose) run --rm -e XDEBUG_MODE=off -e COMPOSER_MEMORY_LIMIT=-1 php composer run-script --timeout=0 fix

coverage: ## Run code coverage
	$(docker_compose) run --rm -e XDEBUG_MODE=coverage -e COMPOSER_MEMORY_LIMIT=-1 php composer run-script --timeout=0 coverage
