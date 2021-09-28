# Authentication and authorization

- [Introduction](#introduction)
- [Authentication](#authentication)
	- [Get started quickly](#getting-started-fast)
	- [Configuration](#configuration)
- [Authorization](#authorization)
	- [Authorizing resource controllers](#authorizing-resource-controllers)
- [Administration](#Administration) 

<a name="introduction"></a>
## Introduction

For authentication using [layered structure](layers.md), the provider `Laravelayers\Auth\ServiceUserProvider` is used.

For authorization, the policy class `Laravelayers\Auth\Policies\Policy` is used, which checks the allowed and prohibited user actions in the database.

<a name="authentication"></a>
## Authentication

<a name="getting-started-fast"></a>
### Get Started Quickly

The easiest way to start using the authentication system is to run the `laravelayers:auth` Artisan command.

> Обратите внимание, что команда выполняется автоматически в процессе [установки Laravelayers](installation.md) и выполнения команды `laravelayers:install --no-interaction`.

This command prompts you to run the `migrate` Artisan command to create database tables to store allowed and denied user actions. To perform all the default actions, use the `--force` option:

```php
php artisan laravelayers:auth
```
	
Also, the `laravelayers:auth` Artisan command will add the required routes to the `routes/web.php` file:

```php
// Auth routes

Route::authLayer();

Route::get('/home', '\Laravelayers\Auth\Controllers\HomeController@index')->name('home')->middleware('verified');
Route::post('/home', '\Laravelayers\Auth\Controllers\HomeController@update');
```

<a name="configuration"></a>
### Configuration

<a name="adding-custom-controllers"></a>
#### Adding custom controllers

The `authLayer` method of the `Illuminate\Support\Facades\Route` facade uses default routes for controllers:

- Login & Logout  `Laravelayers\Auth\Controllers\LoginController`.
- Registering `Laravelayers\Auth\Controllers\RegisterController`.
- Verification of E-mail `Laravelayers\Auth\Controllers\VerificationController`.
- Password recovery `Laravelayers\Auth\Controllers\ForgotPasswordController`.
- Reset Password  `Laravelayers\Auth\Controllers\ResetPasswordController`.

To use your own controllers, just pass the path to the controllers inside the `app` directory to the `authLayer` method of the `Illuminate\Support\Facades\Route` facade, then all found controllers will be used:

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

You can disable registration, password reset and email verification by passing an array of options to the `authLayer` method:

```php
Route::authLayer(['path' => 'Auth', 'register' => false, 'reset' => false, 'verify' => false]);
```

<a name="adding-custom-class-layers"></a>
#### Adding custom class layers

You can add your own [service layer](services.md), [repository](repositories.md), [model](models.md) and policy class in the service provider by implementing the default class contracts:

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
#### Add custom user provider
	
You can override the provider class of the user `Laravelayers\Auth\ServiceUserProvider` in the service provider:

```php
Auth::provider('eloquent', function($app, array $config) {
	return new EloquentUserProvider($this->app['hash'], $config['model']);
});
```
	
<a name="adding-custom-notification-classes"></a>
#### Adding custom notification classes

You can override the classes for sending notifications to the user's E-mail in the service provider:

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
#### Add custom model column names

Default column names are defined in the User, Action, Role, and Role Action models, you can change them in your own models that extend from the base ones: 
 
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
#### Publish submissions and translations

To publish views and translations of authentication, run the command:

```php
php artisan vendor:publish --tag=laravelayers-auth 
```

<a name="authorization"></a>
## Authorization

The implementation of the authorization system is based on the [subsystem for managing administrator privileges](https://phpclub.ru/talk/threads/Подсистема-управления-привилегиями-администраторов.17569/) developed by Yuri Popov.

After executing the `laravelayers:auth` Artisan commands, tables will be created in the database to store allowed and prohibited user actions:

- `user_actions` contains allowed actions of users with a value of `1` in the column `allowed` and forbidden actions with a value of `0`. You can also allow the action only for the specified IP address.
- `user_roles` contains the names of user roles.
- `user_role_actions` contains the allowed and prohibited actions of user roles.

The user action must match the route name, for example, the `admin.auth.users.create` action matches the `/admin/auth/users/create` route named `admin.auth.users.create`.

To authorize user actions, the `check` method of the `Laravelayers\Auth\Policies\Policy` policy class is used, which is executed before any other policy methods.

To determine if a user is authorized to perform an action, you need to pass it as the first argument to the gateway method `allows` or `denies`, to the method of the authenticated user object `can` or `cant`, to the middleware or controller helper:

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
	
> Note that if you pass the second argument, the `check` method of the `Laravelayers\Auth\Policies\Policy` policy class will return `null`, i.e. its execution will be skipped.

Also, you can pass only the name of the action method of the route, then it will be automatically converted to the name of the current route, in which the name of the action method will be replaced with the passed one. For example, for a route named `admin.auth.users.index`, the passed name `create` will be converted to the name `admin.auth.users.create`:

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
	
> Note that in order to undo the conversion to the route name, a point must be added at the beginning or at the end of the passed action. If the route starts with the name of the passed action, then it will not be converted to the name of the route either.

The user action can allow or deny all the names of the route action methods, for example, for a user who has the allowed action `admin.auth.users`, any actions for the `/admin/auth/users` route will be authorized:

```php
Gate::allows('admin.auth.users.index');
	
// true
	
Gate::allows('admin.auth.users.create');
	
// true
```

A user action can allow or deny a group of routes, for example, for a user who has an allowed action `admin`, any actions starting with `admin.` will be authorized, unless they are prohibited for the user:

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
	
> Note that if an action is prohibited for a user, then all nested actions will also be prohibited. 
	
To determine if a user is authorized to perform an action that starts with the action name part, you need to add `.*` at the end instead of the name part, for example, for a user who has an allowed action `admin.auth.users.index` will be authorized action `admin.auth.users.*` etc .:

```php
Gate::allows('admin.auth.users.*');
	
// true
	
Gate::allows('admin.auth.*');
	
// true
	
$this->middleware('can:admin.viewAny');
	
// $this->middleware('can:admin.*');
```

When authorizing user actions, aliases for actions are also checked, for example, for the action `admin.auth.users.view`, the action of the alias `admin.auth.users.show` will also be checked, and vice versa:

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

> Note that using the `setActionAliasMap` static method of the `Laravelayers\Auth\Policies\Policy` class, you can add an action name and alias by passing them as the first and second arguments, or change all aliases by passing an array of actions and aliases as first argument.

**An example of authorization in accordance with the data in the database**
	
An example of the `user_actions` table:

id | action                   | allowed | ip
---|--------------------------|---------|-----------
1  | role.admin               | 1       | 127.0.0.1
1  | admin.auth.users.destroy | 0       | 127.0.0.1

An example of the `user_roles` table:

id | action
---|------------
1  | role.admin

An example of the `user_role_actions` table:

role_id | action
--------|--------------------
1       | admin.auth.users
1       | admin.role
1       | admin.test.index

Authorization example:

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
### Authorizing resource controllers
	
The `authorizeResource` method overrides the base Laravel controller method so that if you use it without passing the first argument, only the action name will be passed to the `can` middleware as a single argument:

```php
$this->authorizeResource();
```
	
The following controller methods will be mapped to their respective user actions i.e. the names of the routes of the resource controller:

Controller method | User action
------------------|-----------------------
index             | *
show              | view
create            | create
store             | create
edit              | update
update            | update
destroy           | delete	

> Note that the user action `*` will be authorized if the user has any allowed action for the controller. For example, when calling  middleware `can:*` for the route `/admin/auth/users`, the action `admin.auth.users.*` will be authorized if the user has any action starting with `admin.auth.users.`

You can also force the route of the resource controller by passing it as the second argument and `null` as the first, in which case the user action `admin.auth.users.viewAny` instead of `admin.auth.users.*` will be mapped to the `index` controller method:

```php
$this->authorizeResource(null, 'admin.auth.users');
```

<a name="Administration"></a>
## Administration

After the first user is registered, provided that the value of the current application environment is `local`, the `role.admistrator` role will be added for the user with the allowed action `admin`.

After [adding routes of the administration panel](admin.md#adding-route), going to the administration panel, sections for managing users, actions, roles and actions of roles will be available.

You can also add your own layer classes to manage users, actions, roles, and role actions in the service provider by implementing the default class contracts:

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
