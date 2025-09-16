.PHONY: up down migrate parse-books

up:
	docker-compose up -d

down:
	docker-compose down

migrate:
	docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction

migrate-diff:
	docker-compose exec php php bin/console doctrine:migrations:diff

composer-install:
	docker-compose exec php composer install

parse-books:
	docker-compose exec php php bin/console app:parse-books

create-admin:
	docker-compose exec php php bin/console app:create-admin

reset-db:
	docker-compose exec php php bin/console doctrine:schema:drop --force --full-database
	docker-compose exec php php bin/console doctrine:schema:update --force