## Halcyon

ITaP Research Computing portal.

This is built on Laravel.

### About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

### Minimum System Requirements

To be able to run Halcyon you have to meet the following requirements:
* PHP >= 7.2.5 or higher
* BCMath PHP Extension
* Ctype PHP Extension
* Fileinfo PHP extension
* JSON PHP Extension
* Mbstring PHP Extension
* OpenSSL PHP Extension
* PDO PHP Extension
* Tokenizer PHP Extension
* XML PHP Extension
* MySql 5.5+ or MariahDB 10.1+

### Install

Clone the repo.

```
git clone https://github.rcac.purdue.edu/RCAC-Staff/halcyon.git .
```

Move into the directory.

```
cd halcyon
```

#### Docker

This will perform the Composer install and initial database migration (found under the manual setup).

```
docker-compose up
```

#### Manual

Run Composer to install dependencies.

```
php ./bin/composer install --prefer-dist
```

Modify the database configuration in `config/database.php` or `.env`.

Run migrations to install tables and base data.

```
php artisan migrate
php artisan module:migrate
```
