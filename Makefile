init: pull-images build up composer-install generate-key-pairs load-fixtures

pull-images:
	docker-compose pull
build:
	docker-compose build
load-fixtures:
	docker-compose exec php bin/console doctrine:fixtures:load -q
generate-key-pairs:
	docker-compose exec php bin/console lexik:jwt:generate-keypair --overwrite
composer-install:
	docker-compose exec php composer install
composer-update:
	docker-compose exec php composer update
up:
	docker-compose up -d
