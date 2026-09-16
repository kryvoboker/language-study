DEV_DOCKER_COMPOSE_FILE=.docker/dev/docker-compose.yml
PART_OF_CONTAINER_NAME=language-study

up-dev:
	docker compose -f $(DEV_DOCKER_COMPOSE_FILE) up -d

down-dev:
	docker compose -f $(DEV_DOCKER_COMPOSE_FILE) down

build-dev:
	docker compose -f $(DEV_DOCKER_COMPOSE_FILE) build

restart-dev: down-dev up-dev
rebuild-dev: down-dev build-dev up-dev

set-node:
	bash -c "source ~/.nvm/nvm.sh && nvm use 25.6.1"

# Open a shell in the PHP-FPM container.
stpt:
	docker compose -f $(DEV_DOCKER_COMPOSE_FILE) exec $(PART_OF_CONTAINER_NAME)-php-fpm /bin/bash

branch-list:
	git branch -a --sort=-committerdate