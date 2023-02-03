## Halcyon

Halcyon is a unified HPC center operations portal. It allows for self-serve customer allocation purchase and management, account management, a knowledge base, customer relations management (CRM), and more.

### Underlying Framework

Halcyon utilizes Laravel, a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

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
* PHP >= 7.3 or higher
* BCMath PHP Extension
* Ctype PHP Extension
* Fileinfo PHP extension
* JSON PHP Extension
* Mbstring PHP Extension
* OpenSSL PHP Extension
* PDO PHP Extension
* Tokenizer PHP Extension
* XML PHP Extension
* MySql 5.6+ or MariahDB 10.1+

### Structure

Extensions typically consist of [modules](https://nwidart.com/laravel-modules/v6/introduction), widgets, listeners, and themes.

```
app/
|_ Listeners/
   |_ Queues/
      |_ lang/
      |_ Queues.php
      |_ listener.json
|_ Modules/
   |_ Users/
      |_ Config/
      |_ Console/
      |_ Database/
         |_ Migrations/
         |_ Seeders/
      |_ Events/
      |_ Http/
         |_ Controllers/
         |_ Middleware/
         |_ Resources/
      |_ Mail/
      |_ Models/
      |_ Providers/
         |_ UsersServiceProvider.php
         |_ RouteServiceProvider.php
      |_ Resources/
         |_ assets/
            |_ js/
               |_ app.js
            |_ css/
               |_ app.css
         |_ lang/
         |_ views/
      |_ Routes/
         |_ admin.php
         |_ api.php
         |_ site.php
      |_ Tests/
      |_ composer.json
      |_ module.json
      |_ package.json
      |_ webpack.mix.js
|_ Themes/
   |_ Admin/
      |_ assets/
         |_ js/
            |_ app.js
         |_ css/
            |_ app.css
      |_ lang/
      |_ views/
      |_ theme.json
|_ Widgets/
   |_ Menu/
      |_ lang/
      |_ views/
      |_ Menu.php
      |_ widget.json
```

### Install

Clone the repo.

```
git clone git@github.com:PurdueRCAC/halcyon.git .
```

Move into the directory.

```
cd halcyon
```

#### Docker
Modify the database configuration in `config/database.php` or `.env`.
If you are using Docker the `DB_HOST` name will be the container name of the MySQL service.

NOTE: You can copy `.env.example` and rename it to `.env` which will have many of the variables pre-filled. 

This will perform the Composer install and initial database migration (found under the manual setup).

```
docker-compose up
```

Install needed libraries

```
docker exec -it halcyon-php-fpm php /var/www/html/bin/composer install --prefer-dist
```

Run the initial setup script

```
docker exec -it halcyon-php-fpm php /var/www/html/bin/composer run-script initial-setup
```

This should complete the setup as it runs through all the individual artisan commands listed below.
However if you make changes/add Modules/ etc. the individual commands to launch the appropriate artisan command via Docker are 
listed below.




Run migrations to install tables and base data.
```
docker exec -it halcyon-php-fpm php artisan migrate
docker exec -it halcyon-php-fpm php artisan module:migrate
```

Create a symlink from the file storage to a publicly accessible spot. This will create a symlink for `./public/files` to `./storage/app/public`.

```
docker exec -it halcyon-php-fpm php artisan storage:link
```

Publish assets.

```
docker exec -it halcyon-php-fpm php artisan module:publish
docker exec -it halcyon-php-fpm php artisan theme:publish
docker exec -it halcyon-php-fpm php artisan listener:publish
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

Create a symlink from the file storage to a publicly accessible spot. This will create a symlink for `./public/files` to `./storage/app/public`.

```
php artisan storage:link
```

Publish assets.

```
php artisan module:publish
php artisan theme:publish
php artisan listener:publish
```

#### Git Hooks

The repo contains some useful git hooks for development.

```
git config core.hooksPath .githooks
```
