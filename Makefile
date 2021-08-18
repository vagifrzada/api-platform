init: pull-images build up composer-install generate-key-pairs migration load-fixtures

pull-images:
	docker-compose pull
build:
	docker-compose build
migration:
	docker-compose exec php bin/console doctrine:migrations:migrate
load-fixtures:
	docker-compose exec php bin/console doctrine:fixtures:load -q
generate-key-pairs:
	docker-compose exec php bin/console lexik:jwt:generate-keypair --overwrite
composer-install:
	docker-compose exec php composer install
composer-update:
	docker-compose exec php composer update
dump-autoload:
	docker-compose exec php composer dump-autoload -o
up:
	docker-compose up -d
tester:
	docker-compose exec php bin/phpunit
