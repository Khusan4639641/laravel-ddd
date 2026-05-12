# Project9

Project9 is a Laravel REST API for products, orders, and payments. The project is organized with a DDD-inspired structure and includes authentication, admin product management, order creation/cancellation, fake payment processing, events, listeners, queue jobs, API resources, form requests, and tests.

## Technology Stack

- PHP 8.3
- Laravel 13
- Laravel Sanctum for API token authentication
- Eloquent ORM in the Infrastructure layer
- Database queues
- PHPUnit feature and unit tests
- Laravel Pint for formatting

## DDD Architecture

The application is split into four main layers:

- `app/Domain` contains entities, value objects, repository interfaces, domain events, and domain exceptions.
- `app/Application` contains DTOs and use cases.
- `app/Infrastructure` contains Eloquent models, repository implementations, services, listeners, and queue jobs.
- `app/Interfaces` contains HTTP controllers, form requests, middleware, and API resources.

HTTP controllers stay thin and delegate work to use cases. Eloquent is not used directly in controllers.

More details: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

If you use SQLite locally:

```bash
touch database/database.sqlite
```

## Environment

Minimum `.env` values for local development:

```dotenv
APP_NAME=Project9
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/project9/database/database.sqlite

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

For MySQL, set `DB_CONNECTION=mysql` and configure `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`.

## Migrations And Seeders

```bash
php artisan migrate:fresh --seed
```

Seeders create:

- `test@example.com` with password `password` and role `user`.
- One active product.
- One inactive product.

To create an admin user for admin routes:

```bash
php artisan tinker
```

```php
App\Models\User::factory()->create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'role' => 'admin',
]);
```

The factory default password is `password`.

## Run The Application

```bash
php artisan serve
```

## Run The Queue

Events dispatch listeners that push queued jobs for notifications and logs. Run a worker with:

```bash
php artisan queue:work --tries=3
```

## Run Tests

```bash
php artisan test
```

Optional formatting:

```bash
./vendor/bin/pint --dirty
```

## API Documentation

Full endpoint list, auth rules, curl examples, and JSON bodies are documented in [docs/API.md](docs/API.md).
