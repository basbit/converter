env=dev
compose=docker-compose -f docker-compose.yml

export compose env

.PHONY: start
start: erase build up db ## clean current environment, recreate dependencies and spin up again

.PHONY: stop
stop: ## stop environment
		$(compose) stop $(s)

.PHONY: rebuild
rebuild: start ## same as start

.PHONY: erase
erase: ## stop and delete containers, clean volumes.
		$(compose) stop
		docker-compose rm -v -f

.PHONY: build
build: ## build environment and initialize composer and project dependencies
		$(compose) build --parallel

		if [ env = "prod" ]; then \
			echo Building in $(env) mode; \
			$(compose) run --rm php sh -lc 'composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --no-suggest --optimize-autoloader'; \
		else \
			$(compose) run --rm php sh -lc 'composer install'; \
		fi

.PHONY: composer-update
composer-update: ## Update project dependencies
		$(compose) run --rm php sh -lc 'composer update'

.PHONY: up
up: ## spin up environment
		$(compose) up -d --remove-orphans

.PHONY: db
db: ## recreate database
		$(compose) exec -T php sh -lc './bin/console d:d:d --force --if-exists'
		$(compose) exec -T php sh -lc './bin/console d:d:c --if-not-exists'
		$(compose) exec -T php sh -lc './bin/console d:m:m -n'

.PHONY: import
import: ## import quotes
		$(compose) exec -T php sh -lc 'php ./bin/console app:import'

.PHONY: schema-validate
schema-validate: ## validate database schema
		$(compose) exec -T php sh -lc './bin/console d:s:v'

.PHONY: sh
sh: ## gets inside a container, use 's' variable to select a service. make s=php sh
		$(compose) exec $(s) sh -l

.PHONY: logs
logs: ## look for 's' service logs, make s=php logs
		$(compose) logs -f $(s)

.PHONY: help
help: ## Display this help message
	@cat $(MAKEFILE_LIST) | grep -e "^[a-zA-Z_\-]*: *.*## *" | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
