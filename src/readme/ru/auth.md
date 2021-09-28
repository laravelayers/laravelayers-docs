# Аутентификация и авторизация

- [Введение](#introduction)
- [Аутентификация](#authentication)
	- [Быстрое начало работы](#getting-started-fast)
	- [Конфигурация](#configuration)
- [Авторизация](#authorization)
	- [Авторизация контроллеров ресурсов](#authorizing-resource-controllers)
- [Администрирование](#Administration) 

<a name="introduction"></a>
## Введение

Для aутентификация с использованием [слоистой структуры](layers.md) используется провайдер `Laravelayers\Auth\ServiceUserProvider`.

Для авторизации используется класс политики `Laravelayers\Auth\Policies\Policy`, который проверяет разрешенные и запрещенные действия пользователя в базе данных.

<a name="authentication"></a>
## Аутентификация

<a name="getting-started-fast"></a>
### Быстрое начало работы

Простейший способ начать использовать систему аутентификации — выполнить Artisan-команду `laravelayers:auth`.

> Обратите внимание, что команда выполняется автоматически в процессе [установки Laravelayers](installation.md) и выполнения команды `laravelayers:install --no-interaction`.

В результате выполнения данной команды будет предложено выполнить Artisan-команду `migrate`, чтобы создать таблицы базы данных для хранения разрешенных и запрещенных действий пользователей. Для выполнения всех действий по умолчанию следует использовать опцию `--force`:

```php
php artisan laravelayers:auth
```
	
Также Artisan-команда `laravelayers:auth` добавит необходимые маршруты в файл `routes/web.php`:

```php
// Auth routes

Route::authLayer();

Route::get('/home', '\Laravelayers\Auth\Controllers\HomeController@index')->name('home')->middleware('verified');
Route::post('/home', '\Laravelayers\Auth\Controllers\HomeController@update');
```

<a name="configuration"></a>
### Конфигурация

<a name="adding-custom-controllers"></a>
#### Добавление пользовательских контроллеров

Метод `authLayer` фасада `Illuminate\Support\Facades\Route` использует маршруты для контроллеров по умолчанию:

- Вход и выход  `Laravelayers\Auth\Controllers\LoginController`.
- Регистрация `Laravelayers\Auth\Controllers\RegisterController`.
- Верификация E-mail `Laravelayers\Auth\Controllers\VerificationController`.
- Восстановление пароля `Laravelayers\Auth\Controllers\ForgotPasswordController`.
- Сброс пароля  `Laravelayers\Auth\Controllers\ResetPasswordController`.

Для использования собственных контроллеров достаточно передать в метод `authLayer` фасада `Illuminate\Support\Facades\Route` путь до контроллеров внутри директории `app`, тогда будут использованы все найденные контроллеры:

```php
// Terminal

php artisan make:controller Auth/LoginController --rp Laravelayers/Auth/Controllers/LoginController

// routes/web.php
	
Route::authLayer('Auth');
	
// Terminal
	
php artisan route:list | grep showLoginForm 
	
/*
	| | GET|HEAD | login | login | App\Http\Controllers\Auth\LoginController@showLoginForm | web,guest |
*/
```

Вы можете отключить регистрацию, сброс пароля и верификацию E-mail, передав массив опций в метод `authLayer`:

```php
Route::authLayer(['path' => 'Auth', 'register' => false, 'reset' => false, 'verify' => false]);
```

<a name="adding-custom-class-layers"></a>
#### Добавление пользовательских слоев классов

Добавить собственный [сервисный слой](services.md), [репозиторий](repositories.md), [модель](models.md) и класс политики, можно в сервис-провайдере, реализовав контракты классов по умолчанию:

```php
<?php
	
namespace App\Providers;
	
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
	
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
	{
		$this->app->bind(
			\Laravelayers\Contracts\Admin\Services\Auth\UserService::class,
			\Laravelayers\Auth\Services\UserService
		);
        
		$this->app->bind(
			\Laravelayers\Contracts\Admin\Repositories\Auth\UserRepository::class,
			\Laravelayers\Auth\Repositories\UserRepository::class
		);
		    
		$this->app->bind(
			\Laravelayers\Contracts\Auth\User::class,
			\Laravelayers\Auth\Models\User::class
		);
    
		$this->app->bind(
			\Laravelayers\Contracts\Auth\Policy::class,
			\Laravelayers\Auth\Policies\Policy::class
		);	        
    }
}
```

<a name="adding-custom-user-provider"></a>
#### Добавление пользовательского провайдера пользователя
	
Переопределить класс провайдера пользователя `Laravelayers\Auth\ServiceUserProvider` можно в сервис-провайдере:

```php
Auth::provider('eloquent', function($app, array $config) {
	return new EloquentUserProvider($this->app['hash'], $config['model']);
});
```
	
<a name="adding-custom-notification-classes"></a>
#### Добавление пользовательских классов уведомлений

Переопределить классы для отправки уведомлений на E-mail пользователя можно в сервис-провайдере:

```php
$this->app->bind(
	\Laravelayers\Auth\Notifications\Registered::class,
	\App\Notifications\Auth\Registered::class
);

$this->app->bind(
	\Laravelayers\Auth\Notifications\ResetPassword::class,
	\App\Notifications\Auth\ResetPassword::class
);

$this->app->bind(
	\Laravelayers\Auth\Notifications\VerifyEmail::class,
	\App\Notifications\Auth\VerifyEmail::class
);
```

<a name="column-names-to-models"></a>
#### Добавление пользовательских имен столбцов моделей

В моделях пользователей, действий, ролей и действий ролей определены имена столбцов по умолчанию, вы можете изменить их в собственных моделях, расширяемых от базовых: 
 
```php   
// Laravelayers\Auth\Models\User
	
protected $loginColumn = 'name';
	
protected $emailColumn = 'email';

protected $emailVerifiedAtColumn = 'email_verified_at';
	
protected $passwordColumn = 'password';
	
protected $fillable = [
	'name', 'email', 'password',
];
	
protected $hidden = [
	'authIdentifierName', 'nameColumn', 'emailColumn', 'password', 'passwordColumn', 'remember_token', 'rememberTokenName'
];	
    
// Laravelayers\Auth\Models\UserAction
	
protected $primaryKey = 'action';	
	
protected $userColumn = 'user_id';
	
protected $actionColumn = 'action';
	
protected $allowedColumn = 'allowed';
	
protected $ipColumn = 'ip';
    
// Laravelayers\Auth\Models\UserRole

protected $roleColumn = 'role';
	
// Laravelayers\Auth\Models\UserRoleAction
	
protected $primaryKey = 'action';
	
protected $roleColumn = 'role_id';
	
protected $actionColumn = 'action';

protected $allowedColumn = 'allowed';
```

<a name="publishing-views-and-translations"></a>
#### Публикация представлений и переводов

Для публикации представлений и переводов аутентификации, выполните команду:

```php
php artisan vendor:publish --tag=laravelayers-auth 
```

<a name="authorization"></a>
## Авторизация

Реализация системы авторизации основана на [подсистеме управления привилегиями администраторов](https://phpclub.ru/talk/threads/Подсистема-управления-привилегиями-администраторов.17569/), разработанной Юрием Поповым.

После выполнения Artisan-команды `laravelayers:auth` будут созданы таблицы в базе данных для хранения разрешенных и запрещенных действий пользователей:

- `user_actions` содержит разрешенные действия пользователей со значением `1` в столбце `allowed` и запрещенные со значение `0`. Также можно разрешить действие только для указанного IP адреса.
- `user_roles` содержит названия ролей пользователей.
- `user_role_actions` содержит разрешенные и запрещенные действия ролей пользователей.

Действие пользователя должно соответствовать имени маршрута, например, действие `admin.auth.users.create` соответствует маршруту `/admin/auth/users/create` с именем `admin.auth.users.create`.

Для авторизации действий пользователя используется метод `check` класса политики `Laravelayers\Auth\Policies\Policy`, который выполняется перед любыми другими методами политики.

Чтобы определить, авторизован ли пользователь для выполнения действия, необходимо передать его в качестве первого аргумента в метод шлюза `allows` или `denies`, в метод объекта аутентифицированного пользователя `can` или `cant`, в перехватчик или помощник контроллера:

```php
Gate::allows('admin.auth.users.create');
	
// true

Gate::denies('admin.auth.users.create');
	
// false
	
Auth::user()->can('admin.auth.users.create');
	
// true
	
Auth::user()->cant('admin.auth.users.create');
	
// false
	
$this->middleware('can:admin.auth.users.create');
	
$this->authorize('admin.auth.users.create');
```
	
> Обратите внимание, что если передать второй аргумент, то метод `check` класса политики `Laravelayers\Auth\Policies\Policy` вернет `null`, т.е. его выполнение будет пропущено.

Также вы можете передать только имя метода действия маршрута, тогда оно будет автоматически преобразовано к имени текущего маршрута, в котором имя метода действия будет заменено на переданное. Например, для маршрута с именем `admin.auth.users.index`, переданное имя `create` будет преобразовано к имени `admin.auth.users.create`:

```php
Gate::allows('create');
	
// Gate::allows('admin.auth.users.create');
	
Auth::user()->can('create.*');
	
// Auth::user()->can('admin.auth.users.create.*');
	
$this->middleware('can:create');
	
// $this->middleware('can:admin.auth.users.create');
	
$this->authorize('.create');
	
// $this->authorize('create');
	
Gate::allows('admin');
	
// true
```
	
> Обратите внимание, что для того чтобы отменить преобразование к имени маршрута, необходимо добавить точку в начале или в конце переданного действия. Если маршрут начинается с имени переданного действия, то он также не будет преобразован к имени маршрута.

Действие пользователя может разрешать или запрещать все имена методов действий маршрута, например, для пользователя, у которого есть разрешенное действие `admin.auth.users`, будут авторизованы любые действия для маршрута `/admin/auth/users`:

```php
Gate::allows('admin.auth.users.index');
	
// true
	
Gate::allows('admin.auth.users.create');
	
// true
```

Действие пользователя может разрешать или запрещать группу маршрутов, например, для пользователя, у которого есть разрешенное действие `admin`, будут авторизованы любые действия начинающиеся с `admin.`, если они не запрещены для пользователя:

```php
Gate::allows('admin');
	
// true
	
Gate::allows('admin.auth.users.index');
	
// true
	
Gate::allows('admin.auth.roles');
	
// false
	
Gate::allows('admin.auth.roles.users.index');
	
// false
```
	
> Обратите внимание, что если для пользователя запрещено действие, то все вложенные действия будут также запрещены. 
	
Чтобы определить, авторизован ли пользователь для выполнения действия, начинающегося с части имени действия, необходимо добавить `.*` в конце вместо части имени, например, для пользователя, у которого есть разрешенное действие `admin.auth.users.index`, будет авторизовано действие `admin.auth.users.*` и т.д.:

```php
Gate::allows('admin.auth.users.*');
	
// true
	
Gate::allows('admin.auth.*');
	
// true
	
$this->middleware('can:admin.viewAny');
	
// $this->middleware('can:admin.*');
```

При авторизации действий пользователя, также проверяются псевдонимы для действий, например, для действия `admin.auth.users.view`, также будет проверено действие псевдонима `admin.auth.users.show`, и наоборот:

```php
Laravelayers\Auth\Policies\Policy::getActionAliasMap();
	
/*
	array:5 [
		'view' => 'show',
		'viewAny' => 'index',
		'create' => 'add',
		'update' => 'edit',
		'delete' => 'destroy'
	]
*/
	
Gate::allows('admin.auth.users.view');
	
// true
	
Gate::allows('admin.auth.users.show');
	
// true
```

> Обратит внимание, что с помощью статического метода `setActionAliasMap` класса `Laravelayers\Auth\Policies\Policy` можно добавить имя действия и псевдоним, передав их в качестве первого и второго аргументов, или изменить все псевдонимы, передав массив действий и псевдонимов в качестве первого аргумента.

**Пример авторизации в соответствии с данными в БД**
	
Пример таблицы `user_actions`:

id | action                   | allowed | ip
---|--------------------------|---------|-----------
1  | role.admin               | 1       | 127.0.0.1
1  | admin.auth.users.destroy | 0       | 127.0.0.1

Пример таблицы `user_roles`:

id | action
---|------------
1  | role.admin

Пример таблицы `user_role_actions`:

role_id | action
--------|--------------------
1       | admin.auth.users
1       | admin.role
1       | admin.test.index

Пример авторизации:

```php
Request::ip(); 
	
// 127.0.0.1

Gate::allows('role.admin'); // true
	
Gate::allows('admin.auth.users'); // true
Gate::allows('admin.auth.users.*'); // true
Gate::allows('admin.auth.users.destroy'); // false	
Gate::allows('admin.roles'); // true
Gate::allows('admin.roles.destroy'); // true
	
Gate::allows('admin.test'); // false
Gate::allows('admin.test.index'); // true
Gate::allows('admin.test.*'); // true
	
Request::ip();
	
// 172.16.10.1

Gate::allows('role.admin'); // false
	
Gate::allows('admin.auth.users'); // false
	
Gate::allows('admin.auth.users.destroy'); // true
```
	
<a name="authorizing-resource-controllers"></a>
### Авторизация контроллеров ресурсов
	
Метод `authorizeResource` переопределяет метод базового контроллера Laravel, таким образом, что если его использовать без передачи первого аргумента, то в перехватчик `can` будет передаваться только имя действия в качестве единственного аргумента:

```php
$this->authorizeResource();
```
	
Следующие методы контроллера будут сопоставлены с их соответствующими действиями пользователя, т.е. именами методов маршрутов контроллера ресурса:

Метод контроллера | Действие пользователя
------------------|-----------------------
index             | *
show              | view
create            | create
store             | create
edit              | update
update            | update
destroy           | delete	

> Обратите внимание, что действие пользователя `*` будет авторизовано при существовании у пользователя любого разрешенного действия для контроллера. Например, при вызове  перехватчика `can:*` для маршрута `/admin/auth/users` будет авторизовано действие `admin.auth.users.*` при существовании у пользователя любого действия начинающегося с `admin.auth.users.`

Так же можно принудительно указать маршрут контроллера ресурса, передав его в качестве второго аргумента и `null` в качестве первого, в таком случае для метода контроллера `index` будет сопоставлено действие пользователя `admin.auth.users.viewAny` вместо `admin.auth.users.*`:

```php
$this->authorizeResource(null, 'admin.auth.users');
```

<a name="Administration"></a>
## Администрирование

После регистрации первого пользователя, при условии, что значение текущей среды приложения будет равно `local`, для пользователя будет добавлена роль `role.admistrator` с разрешенным действием `admin`.

После [добавления маршрутов панели администрирования](admin.md#adding-route), перейдя в панель администрирования, будут доступны разделы для управления пользователями, действиями, ролями и действиями ролей.

Вы также можете добавить собственные классы слоев для управления пользователями, действиями, ролями и действиями ролей в сервис-провайдере, реализовав контракты классов по умолчанию:

```php
<?php
	
namespace App\Providers;
	
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
	
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
	{
		$this->registerUserService();

		$this->registerUserRepository();

		$this->registerUserModel();	        
    }
    
	/**
	 * Register the user service.
	 *
	 * @return void
	 */
	public function registerUserService()
	{
		$this->app->bind(
			\Laravelayers\Contracts\Admin\Services\Auth\UserService::class,
			\Laravelayers\Admin\Services\Auth\UserService::class
		);
		
		$this->app->bind(
			\Laravelayers\Contracts\Admin\Services\Auth\UserActionService::class,
			\Laravelayers\Admin\Services\Auth\UserActionService::class
		);

		$this->app->bind(
			\Laravelayers\Contracts\Admin\Services\Auth\UserRoleService::class,
			\Laravelayers\Admin\Services\Auth\UserRoleService::class
		);
		
		$this->app->bind(
			\Laravelayers\Contracts\Admin\Services\Auth\UserRoleActionService::class,
			\Laravelayers\Admin\Services\Auth\UserRoleActionService::class
		);
		
		$this->app->bind(
			\Laravelayers\Contracts\Admin\Services\Auth\RoleUserService::class,
			\Laravelayers\Admin\Services\Auth\RoleUserService::class
		);
	}
	
	/**
	 * Register the user repository.
	 *
	 * @return void
	 */
	public function registerUserRepository()
	{
		$this->app->bind(
			\Laravelayers\Contracts\Admin\Repositories\Auth\UserRepository::class,
			\Laravelayers\Admin\Repositories\Auth\UserRepository::class
		);
		
		$this->app->bind(
			\Laravelayers\Contracts\Admin\Repositories\Auth\UserActionRepository::class,
			\Laravelayers\Admin\Repositories\Auth\UserActionRepository::class
		);

		$this->app->bind(
			\Laravelayers\Contracts\Admin\Repositories\Auth\UserRoleRepository::class,
			\Laravelayers\Admin\Repositories\Auth\UserRoleRepository::class
		);

		$this->app->bind(
			\Laravelayers\Contracts\Admin\Repositories\Auth\UserRoleActionRepository::class,
			\Laravelayers\Admin\Repositories\Auth\UserRoleActionRepository::class
		);
	}
	
	/**
	 * Register the user model.
	 *
	 * @return void
	 */
	public function registerUserModel()
	{
		$this->app->bind(
			\Laravelayers\Contracts\Auth\User::class,
			\Laravelayers\Auth\Models\User::class
		);

		$this->app->bind(
			\Laravelayers\Contracts\Auth\UserAction::class,
			\Laravelayers\Auth\Models\UserAction::class
		);

		$this->app->bind(
			\Laravelayers\Contracts\Auth\UserRole::class,
			\Laravelayers\Auth\Models\UserRole::class
		);

		$this->app->bind(
			\Laravelayers\Contracts\Auth\UserRoleAction::class,
			\Laravelayers\Auth\Models\UserRoleAction::class
		);
	}				
}
```
