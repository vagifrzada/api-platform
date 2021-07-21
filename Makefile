init: pull-images build up composer-install

pull-images:
	docker-compose pull
build:
	docker-compose build
load-fixtures:
	docker-compose exec php bin/console doctrine:fixtures:load --append
composer-install:
	docker-compose exec php composer install
composer-update:
	docker-compose exec php composer update
up:
	docker-compose up -d
