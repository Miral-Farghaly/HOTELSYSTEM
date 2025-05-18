# Luxury Hotel Management System

A comprehensive hotel management system built with Laravel, featuring room booking, payment processing, and administrative tools.

## Features

- 🏨 Room Management
- 📅 Booking System
- 💳 Payment Processing
- 👥 User Management
- 🔐 Role-based Access Control
- 📊 Analytics Dashboard
- 🌍 Multi-language Support
- 🔍 SEO Optimization
- 📱 Responsive Design
- 🔄 Real-time Updates

## Tech Stack

- **Backend:** Laravel 10.x
- **Database:** MySQL 8.0
- **Cache:** Redis
- **Frontend:** Vue.js/React
- **CSS:** Tailwind CSS
- **Testing:** PHPUnit
- **Documentation:** Swagger/OpenAPI
- **Queue:** Redis/Laravel Queue
- **Search:** Elasticsearch (optional)

## Prerequisites

- PHP >= 8.1
- Composer
- Node.js >= 18.x
- MySQL >= 8.0
- Redis >= 6.0
- Docker (optional)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/hotel-system.git
cd hotel-system
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Set up environment:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel_system
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run migrations and seeders:
```bash
php artisan migrate --seed
```

7. Build assets:
```bash
npm run build
```

8. Start the development server:
```bash
php artisan serve
```

## Docker Setup

1. Build and start containers:
```bash
docker-compose up -d --build
```

2. Install dependencies:
```bash
docker-compose exec app composer install
docker-compose exec app npm install
```

3. Run migrations:
```bash
docker-compose exec app php artisan migrate --seed
```

## Testing

Run all tests:
```bash
php artisan test
```

Run specific test suite:
```bash
php artisan test --testsuite=Feature
```

## Code Quality

We use several tools to maintain code quality:

- PHP_CodeSniffer (PSR-12)
- PHPStan (Level 8)
- Laravel Pint
- ESLint

Run all checks:
```bash
composer check
```

## API Documentation

API documentation is available at `/api/documentation` when the application is running.

To generate API documentation:
```bash
php artisan l5-swagger:generate
```

## Deployment

1. Configure production environment:
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

2. Set up supervisor for queue processing:
```bash
supervisor queue:work --tries=3
```

3. Configure scheduled tasks:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Security

For security vulnerabilities, please email security@yourdomain.com.

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## Support

For support, email support@yourdomain.com or create an issue in the repository.

## Acknowledgments

- Laravel Team
- All contributors
- Open source community
