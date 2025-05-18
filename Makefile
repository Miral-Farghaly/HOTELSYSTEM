.PHONY: help install test deploy setup docker-up docker-down lint analyze cache-clear

help: ## Show this help
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install: ## Install dependencies
	composer install
	npm install
	php artisan key:generate
	php artisan storage:link

setup: ## Initial setup for development
	cp .env.example .env
	make install
	php artisan migrate:fresh --seed

docker-up: ## Start Docker containers
	docker-compose up -d
	@echo "Waiting for containers to be ready..."
	@sleep 10
	docker-compose exec app php artisan migrate
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

docker-down: ## Stop Docker containers
	docker-compose down

docker-rebuild: ## Rebuild Docker containers
	docker-compose down
	docker-compose build --no-cache
	docker-compose up -d

test: ## Run tests
	php artisan test --parallel
	npm run test

test-coverage: ## Run tests with coverage report
	XDEBUG_MODE=coverage php artisan test --coverage --min=80

lint: ## Run PHP_CodeSniffer
	./vendor/bin/phpcs
	npm run lint

lint-fix: ## Fix code style issues
	./vendor/bin/phpcbf
	npm run lint:fix

analyze: ## Run static analysis
	./vendor/bin/phpstan analyse

cache-clear: ## Clear application cache
	php artisan cache:clear
	php artisan config:clear
	php artisan route:clear
	php artisan view:clear
	composer dump-autoload

cache-warm: ## Warm up application cache
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
	php artisan cache:warmup

deploy-staging: ## Deploy to staging
	php artisan down
	git pull origin develop
	composer install --no-dev --optimize-autoloader
	php artisan migrate --force
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
	npm install
	npm run build
	php artisan up

deploy-prod: ## Deploy to production
	php artisan down
	git pull origin main
	composer install --no-dev --optimize-autoloader
	php artisan migrate --force
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
	npm install
	npm run build
	php artisan up

backup: ## Create database backup
	php artisan backup:run

logs: ## Show application logs
	tail -f storage/logs/laravel.log

queue-work: ## Start queue worker
	php artisan queue:work --tries=3

scheduler: ## Start scheduler
	php artisan schedule:work 