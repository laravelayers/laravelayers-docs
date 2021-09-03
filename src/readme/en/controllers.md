# Controllers

- [Introduction](#introduction)
- [Defining controllers](#defining-controllers)
	- [Injecting service layer](#inject-service-layer)
- [Base controller](#base-controller)

<a name="introduction"></a>
## Introduction

In the [layer](layers.md) controller, a service layer is injected, which is used to process the incoming request, the resulting result is used to return the response.

**General UML Layered Structure Diagram**

[![General UML diagram of the layered structure](../../storage/images/general-uml-diagram-of-the-layered-structure.svg)](https://lucid.app/documents/view/16a364a8-19b7-4136-a555-02f58b0c696e)

**UML diagram of the sequence of interaction of layers**

[![UML diagram of the sequence](../../storage/images/uml-diagram-of-the-sequence.svg)
](https://lucid.app/documents/view/6c217ff3-3a7a-4806-bdf8-7a9b117c9de4)

<a name="defining-controllers"></a>
## Defining controllers

Below is a controller class with an embedded [service layer](services.md). Note that the controller extends the base controller class included with Laravelayers.

```php
<?php
	
namespace App\Http\Controllers\Character;
	
use Laravelayers\Foundation\Controllers\Controller;
	
class CharacterController extends Controller
{
    /**
     * Create a new CharacterController instance.
     *
     * @param CharacterService $characterService
     */
    public function __construct(CharacterService $characterService)
    {
        $this->service = $characterService;
    }
    
    /**
     * Display a listing of repository items.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $items = $this->service->paginate($request);
	
        return view("character.index", compact('items'));
    }
    
	 /**
     * Display the specified repository item.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $item = $this->service->find($id);
	
        return view('character.show', compact('item'));
    }
}
```

<a name="inject-service-layer"></a>	
### Implementing the service layer
	
The easiest way to create a controller with an embedded service layer is to run the `make: controller` Artisan command with the `--service` option:

```php
php artisan make:controller Character/Character -s
```

As a result of this command, the following classes will be created:

- Controller `App\Http\Controllers\Character\CharacterController`.
- Service `App\Services\Character\CharacterService`.
- Repository `App\Repositories\Character\CharacterRepository`.
- Decorator `App\Decorators\Character\CharacterDecorator`.
- Model `App\Models\Character\Character`.

UML class structure diagram:

[![UML diagram of class structure  in Laravelayers](../../storage/images/uml-diagram-of-class-structure.svg)](https://lucid.app/documents/view/4f287ac4-f718-4216-b508-105fa1b035f4)

The specified subdirectory `Character` will be created in the directory for each layer. When creating views, it is recommended that you also create an appropriate subdirectory, for example, `resources/views/character/index.blade.php`.

Class names other than the model will contain a postfix corresponding to the layer name `CharacterController`. By default, all names of classes, files and directories will be converted to a CamelCase string with a capital letter at the beginning. To cancel conversion, use the `--nm` option:

```php
php artisan make:controller Character/Character --service --nm
```

To change the name of the class of the service layer, use the `--sn` option:

```php
php artisan make:controller Character/Character --service --sn Book/Character
```

To change the name of the base controller class, use the `--rp` option:

```php
php artisan make:controller Character/Character --service --rp App/Http/Controllers/BaseController
```

Or, override the call to the create controller command in the service provider:

```php
<?php
	
namespace App\Providers;
	
use Illuminate\Support\ServiceProvider;
	
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->extend('command.controller.make', function () {
            return $this->app
                ->make(\Laravelayers\Foundation\Console\Commands\ControllerMakeCommand::class)
                ->setBaseClass('App/Http/Controllers/BaseController');
        });
    }
}
```
	
You can also customize the stub files used when executing the create controller command. To do this, run the command to publish the most common stubs:

```php
php artisan stub:publish
```
	
<a name="base-controller"></a>
## Base controller

The base controller class `Laravelayers\Foundation\Controllers\Controller` extends the base controller class included in Laravel, define a private `service` property for the service object and additional methods:

- [`authorizeResource`](#authorize-resource)
- [`getPerPage`](#get-per-page)
- [`getSorting`](#get-sorting)
- [`getStatus`](#get-status)

**`authorizeResource()`**

The [`authorizeResource`](auth.md#authorizing-resource-controllers) method overrides the base Laravel controller method.

**`getPerPage()`**

The `getPerPage` method returns the result of calling the [`getPerPage`](services.md#get-per-page) method of the service.

**`getSorting()`**

The `getSorting` method returns the result of calling the [`getSorting`](services.md#get-sorting) method of the service.

```php
request()->route()->getController()->getSorting();
```

**`getStatus()`**

The `getStatus` method returns the result of calling the [`getStatus`](services.md#get-status) method of the service.