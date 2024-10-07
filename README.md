# Halcyon

Halcyon is a unified High Performance Computing (HPC) center operations portal. It allows for self-serve customer allocation purchase and management, account management, a knowledge base, customer relations management (CRM), and more.

## Underlying Framework

Halcyon utilizes [Laravel](https://laravel.com/), a web application framework with expressive, elegant syntax. Laravel takes the pain out of development by easing common tasks used in many web projects.

## Minimum System Requirements

To be able to run Halcyon you have to meet the following requirements:

### Server Requirements

* PHP >= 8.1 or higher
* Ctype PHP Extension
* cURL PHP Extension
* DOM PHP Extension
* Fileinfo PHP extension
* Filter PHP Extension
* Hash PHP Extension
* JSON PHP Extension
* Mbstring PHP Extension
* OpenSSL PHP Extension
* PDO PHP Extension
* Session PHP Extension
* Tokenizer PHP Extension
* XML PHP Extension

### Database Requirements

* MySql 5.6+ or MariahDB 10.1+

## Extensions Overview

Extensions typically consist of modules, widgets, listeners (plugins), and themes.

### Modules

Modules are found in `/app/Modules` and can be thought of as relatively self-contained apps. These can have their own functionality, database tables, interfaces, console commands, and more. Examples include a forum, a store, etc. For further information on creating modules, see the official [Laravel Modules documentation](https://nwidart.com/laravel-modules/v6/introduction).

**Note:** The referenced documentation above places modules in `./Modules`. In Halcyon, they're found in `./app/Modules`. Aside from location, all other documentation should still apply.

### Widgets

Widgets are found in `/app/Widgets` and are small bits of code that can be injected in different locations on a theme or page-by-page basis. Some widgets are linked to modules, displaying information specific to or feeding information to that module. An example of this would be a "Report a problem" widget that presents a form on every page for creating an entry in a support ticketing module. However, widgets do not need to be linked to modules; they can be just static HTML or text.

### Listeners

Listeners (a.k.a., plugins) are found in `/app/Listeners`. Listeners can enhance data and provide additional functionality by executing code in response to certain [events](https://laravel.com/docs/10.x/events). Events serve as a way to decouple aspects of an application, since a single event can have multiple listeners that do not depend on each other. An example may be a listener that sends a notification to Slack when someone places a new order.

### Themes

Themes are found in `/app/Themes`. A theme controls the presentation of the content. The theme is the basic foundation design for viewing the website.

## Directory Structure

Below is an example of the location and typical directory structure typical extensions to the system:

```lua
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

## Installation Guide

### Clone the repository

```bash
git clone git@github.com:PurdueRCAC/halcyon.git .
```

### Navigate into the directory:

```bash
cd halcyon
```

### Docker Setup

1. Modify the database configuration in `config/database.php` or `.env`.
If you are using Docker the `DB_HOST` name will be the container name of the MySQL service.

**NOTE :** You can copy `.env.example` and rename it to `.env` which will have many of the variables pre-filled. 

2. Bring up the docker containers and run the command below. This will perform the Composer install and initial database migration (found under the manual setup).

```bash
docker-compose up
```

3. Install needed libraries:

```bash
docker exec -it halcyon-php-fpm php /var/www/html/bin/composer install --prefer-dist
```

4. Run the initial setup script:

```bash
docker exec -it halcyon-php-fpm php /var/www/html/bin/composer run-script initial-setup
```

#### NOTE : The command above should complete the setup as it runs through all the individual artisan commands listed [within the lines](#commands).
#### However if you make changes/add Modules, etc. the individual commands to run the appropriate artisan command via Docker are listed below.

- - - -
- - - -
<span id="commands">

* Run migrations to install tables and base data:

```bash
docker exec -it halcyon-php-fpm php artisan migrate
docker exec -it halcyon-php-fpm php artisan module:migrate
```

* Create a symlink from the file storage to a publicly accessible spot. 
This will create a symlink for `./public/files` to `./storage/app/public`.

```bash
docker exec -it halcyon-php-fpm php artisan storage:link
```

* Publish assets:

```bash
docker exec -it halcyon-php-fpm php artisan module:publish
docker exec -it halcyon-php-fpm php artisan theme:publish
docker exec -it halcyon-php-fpm php artisan listener:publish
```
- - - -
- - - -
</span>

![Halcyon alt](https://www.rcac.purdue.edu/files/Halcyon%20HPC%20at%20Purdue%27s%20Rosen%20Center.jpg "Halcyon HPC")

### Manual Installation

* Run Composer to install dependencies:

```bash
php ./bin/composer install --prefer-dist
```

* Modify the database configuration in `config/database.php` or `.env`.

* Run migrations to install tables and base data:

```bash
php artisan migrate
php artisan module:migrate
```

* Create a symlink from the file storage to a publicly accessible spot. 
This will create a symlink for `./public/files` to `./storage/app/public`.

```bash
php artisan storage:link
```

* Publish assets:

```bash
php artisan module:publish
php artisan theme:publish
php artisan listener:publish
```

### Create Administrator Account

Regardless of whether you use Docker or manual installation, you will likely need to create an administrator account. Follow the prompts using the command below:

```bash
php artisan users:create
```

### Git Hooks

The repository contains some useful git hooks for development.
To enable them:

```bash
git config core.hooksPath .githooks
```
