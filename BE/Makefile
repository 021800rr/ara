SHELL := /bin/bash

tests:
	symfony console cache:pool:clear cache.global_clearer
	symfony console doctrine:database:drop --force --env=test || true
	symfony console doctrine:database:create --env=test
	symfony console doctrine:migrations:migrate -n --env=test
	symfony console doctrine:fixtures:load -n --env=test
	symfony console cache:pool:clear cache.global_clearer
	php bin/phpunit
	vendor/bin/phpstan analyse -l 9 --memory-limit 1G src tests
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src
.PHONY: tests
