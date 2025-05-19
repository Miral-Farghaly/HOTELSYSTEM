#!/bin/bash

# Create storage directory structure if it doesn't exist
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Set proper permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate application key if not already set
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
    docker-compose exec app php artisan key:generate
fi

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install

# Run migrations and seeders
docker-compose exec app php artisan migrate --seed

# Clear caches
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

echo "Setup completed successfully!" 