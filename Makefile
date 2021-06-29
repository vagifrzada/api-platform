init: pull-images build up

pull-images:
	docker-compose pull
build:
	docker-compose build
composer-install:
	docker-compose run --rm php-cli composer install
composer-update:
	docker-compose run --rm php-cli composer update
up:
	docker-compose up -d
