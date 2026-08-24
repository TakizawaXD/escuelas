.PHONY: install migrate seed backup test format lint docker-up docker-down

install:
	bash scripts/install.sh

migrate:
	bash scripts/migrate.sh

seed:
	bash scripts/seed.sh

backup:
	bash scripts/backup.sh

test:
	vendor/bin/phpunit

format:
	vendor/bin/php-cs-fixer fix .

lint:
	vendor/bin/php-cs-fixer fix . --dry-run --diff

docker-up:
	docker-compose up -d --build

docker-down:
	docker-compose down
