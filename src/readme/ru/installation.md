# Установка

- [Установка и компиляция](#installation-and-compilation)
- [Конфигурация](#configuration)

<a name="installation-and-compilation"></a>
## Установка и компиляция

Во-первых, необходимо установить [фреймворк Laravel](https://laravel.com/) с помощью [Composer](https://getcomposer.org/):

```php
composer create-project --prefer-dist laravel/laravel $HOME/sites/laravelayers "5.7.*"
```	

> Обратите внимание, что для установки используется директория `$HOME/sites/laravelayers`.

Во-вторых, установите Laravelayers, добавив зависимость с помощью Composer:

```php
composer require laravelayers/laravelayers "1.0.*"
```	

> Обратите внимание, что сначала необходимо перейти в директорию в которую вы установили Laravel, например, `cd $HOME/sites/laravelayers`.

В результате вместе с Laravelayers будут установлены следующие зависимости:

- [Intervention Image](https://github.com/Intervention/image)
- [Laravel Phone](https://github.com/Propaganistas/Laravel-Phone)

В-третьих, установите [фронтенд фреймворк Foundation и другие NPM пакеты](frontend.md), для этого сначала выполните следующую Artisan-команду: 

```php
php artisan preset laravelayers-foundation --no-interaction
```
	
> Обратите внимание, что в результате выполнения команды с опцией `--no-interaction` будут добавлены маршруты для [администрирования](admin.md) и [аутентификации](auth.md) в файл `/routes/web.php`.

В-четвертых, установите зависимости из `package.json` и скомпилируйте CSS и JS файлы:

```php	
npm install && npm run dev
```

Дополнительно вы можете установить [пакет Composer для просмотра документации локально](https://github.com/laravelayers/laravelayers-docs/blob/master/README.md) после установки Laravelayers.

<a name="configuration"></a>
## Конфигурация

Не забудьте настроить конфигурацию для вашей среды в файле `.env`.

Установите значение `false` для конфигурационной переменной `database.connections.mysql.strict`:

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

Директории `storage` и `bootstrap/cache` должны быть доступны для записи вашим веб-сервером:

```php
chmod -R 777 storage && chmod -R 777 bootstrap/cache
```

Вы должны создать символическую ссылку из `public/storage` в `storage/app/public`:

```php
php artisan storage:link
```

Дополнительно вы можете настроить [значения конфигурации для отображения даты и времени](date.md).