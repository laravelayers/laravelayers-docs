# Installation

- [Installation and compilation](#installation-and-compilation)
- [Configuration](#configuration)

<a name="installation-and-compilation"></a>
## Installation and compilation

First, you need to install [Laravel Framework](https://laravel.com/) using [Composer](https://getcomposer.org/):

```php
composer create-project --prefer-dist laravel/laravel $HOME/sites/laravelayers "6.*"
```	

> Note that the installation uses the `$HOME/sites/laravelayers` directory.

Second, install Laravelayers by adding a dependency using Composer:

```php
composer require laravelayers/laravelayers "2.*"
```	

> Note that you first need to navigate to the directory where you installed Laravel, for example, `cd $HOME/sites/laravelayers`.

As a result, the following dependencies will be installed along with Laravelayers:

- [Intervention Image](https://github.com/Intervention/image)
- [Laravel Phone](https://github.com/Propaganistas/Laravel-Phone)

Third, install the [Foundation frontend framework and other NPM packages](frontend.md) by first running the following Artisan command: 

```php
php artisan preset laravelayers-foundation --no-interaction
```
	
> Note that running the command with the `--no-interaction` option will add routes for [administration](admin.md) and [authentication](auth.md) to the `/routes/web.php` file.

Fourth, install the dependencies from `package.json` and compile the CSS and JS files:

```php	
npm install && npm run dev
```

Optionally, you can install the [Composer package for viewing documentation locally](https://github.com/laravelayers/laravelayers-docs/blob/master/README.md) after installing Laravelayers.

<a name="configuration"></a>
## Configuration

Don't forget to set the configuration for your environment in the `.env` file.

Set the `database.connections.mysql.strict` configuration variable to `false`:

```php
// config/database.php

'connections' => [
	...
	'mysql' => [
		...
		'strict' => true,
		...
	],
	...
]
```

The `storage` and `bootstrap/cache` directories must be writable by your web server:

```php
chmod -R 777 storage && chmod -R 777 bootstrap/cache
```

You must create a symbolic link from `public/storage` to `storage/app/public`:

```php
php artisan storage:link
```

Additionally, you can customize [configuration values for date and time display](date.md).