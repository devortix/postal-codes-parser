SHELL=/bin/bash
# https://makefiletutorial.com


#### FRONTEND ####
frontend-init: frontend-npm-install

frontend-npm-install:
	docker compose run --rm frontend-node-cli npm install --legacy-peer-deps

frontend-npm-upgrade:
	docker compose run --rm frontend-node-cli npm upgrade
#### FRONTEND ####


PHP_RUN:=docker compose run --rm php-cli

#### ARTISAN ####
artisan:
	$(PHP_RUN) php artisan $^
#### ARTISAN ####


#### COMPOSER ####
composer-install:
	$(PHP_RUN) composer i

composer-update:
	$(PHP_RUN) composer u -vv

composer-remove:
	$(PHP_RUN) composer remove

composer-dump-autoload:
	$(PHP_RUN) composer dump-autoload
#### COMPOSER ####



