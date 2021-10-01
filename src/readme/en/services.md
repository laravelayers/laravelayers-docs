# Service layer

- [Introduction](#introduction)
- [Defining services](#defining-services)
	- [Injecting repository](#inject-repository)
- [Base service](#base-service)	

<a name="introduction"></a>
## Introduction

The service [layer](layers.md) is embedded in the controller, processes the requests coming from the controller, i.e. uses conditions by which the necessary methods of the embedded repository are called, which returns the transformed data in the decorator object.

**General UML Layered Structure Diagram**

[![General UML diagram of the layered structure](../../storage/images/general-uml-diagram-of-the-layered-structure.svg)](https://lucid.app/documents/view/16a364a8-19b7-4136-a555-02f58b0c696e)

**UML diagram of the sequence of interaction of layers**

[![UML diagram of the sequence](../../storage/images/uml-diagram-of-the-sequence.svg)
](https://lucid.app/documents/view/6c217ff3-3a7a-4806-bdf8-7a9b117c9de4)

<a name="defining-services"></a>
## Defining services

Below is a service class with an embedded [repository](repositories.md). Also, a decorator is set in the service using the [`setDecorators`](#set-decorators) method.

> Note that the service extends the base service class included with Laravelayers.

```php
<?php
	
namespace App\Services\Character;
	
use App\Decorators\Character\CharacterDecorator;
use App\Repositories\Character\CharacterRepository;
use Laravelayers\Foundation\Services\Service;
	
class CharacterService extends Service
{
	/**
	 * Create a new CharacterService instance.
	 *
	 * @param CharacterRepository $characterRepository
	 */
	public function __construct(CharacterRepository $characterRepository)
	{
	    $this->repository = $characterRepository;
		
	    $this->setDecorators([
	        CharacterDecorator::class
	    ]);
	}
		
	/**
	 * Find the repository item by the specified ID.
	 *
	 * @param int $id
	 * @return CharacterDecorator
	 */
	public function find($id)
	{         	
		return $this->repository
			->withBooks()
			->findOrFail($id)
	}
    
	/**
	 * Paginate repository items.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Laravelayers\Foundation\Pagination\Decorators\PaginatorDecorator
	 */
	public function paginate(Request $request)
	{
		if ($request->has('search')) {
			$this->repository->search($request->get('search'));
		}
		
		return $this->repository
	    	->withBooks()
	    	->sort($request->get('desc') ? 'desc' : 'asc')
	    	->paginate();
	}
}
```

<a name="inject-repository"></a>	
### Injecting a repository
	
The simplest way to create a service with an embedded repository and a decorator for the repository is to run the `make:service` Artisan command with the `--repository` and `--decorator` options:

```php
php artisan make:service Character/Character -r -d
```

As a result of this command, the following classes will be created:

- Service `App\Services\Character\CharacterService`.
- Repository `App\Repositories\Character\CharacterRepository`.
- Decorator `App\Decorators\Character\CharacterDecorator`.
- Model `App\Models\Character\Character`.

UML class structure diagram:

[![UML diagram of class structure  in Laravelayers](../../storage/images/uml-diagram-of-class-structure.svg)](https://lucid.app/documents/view/4f287ac4-f718-4216-b508-105fa1b035f4)

The specified subdirectory `Character` will be created in the directory for each layer.

By default, services are created in the `app/Services` directory, but if you specify a slash `/Character/Character` at the beginning of the service name, then the service will be created in the application directory `app/Character/CharacterService`.

Class names other than the model will contain a postfix corresponding to the layer name `CharacterService`. By default, all names of classes, files and directories will be converted to a CamelCase string with a capital letter at the beginning. To cancel conversion, use the `--nm` option:

```php
php artisan make:service Character/Character --repository --decorator --nm
```

To change the name of the repository class, use the `--rn` option:

```php
php artisan make:service Character/Character --repository --decorator --rn Book/Character
```
	
To change the name of the decorator class, use the `--dn` option:

```php
php artisan make:service Character/Character --repository --decorator --dn Book/Character
```

To create a collection decorator, use the `--collection` option:	

```php
php artisan make:service Character/Character --repository -c
```

To change the name of the base service class, use the `--rp` option:

```php
php artisan make:service -s Character/Character --rp App/Services/BaseService
```

Or, override the call to the service creation command in the service provider:

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
        $this->app->extend('command.service.make', function () {
            return $this->app
                ->make(\Laravelayers\Foundation\Console\Commands\ServiceMakeCommand::class)
                ->setBaseClass('App/Services/BaseService');
        });
    }
}
```
	
You can also customize the stub files used when executing the command to create a service layer. To do this, run the command to publish the most common stubs:

```php
php artisan stub:publish
```

<a name="base-service"></a>
## Basic service

The base service class `Laravelayers\Foundation\Services\Service` will define a private` repository` property for the repository object and additional methods:

- [`setDecorators`](#set-decorators)
- [`getFormElements`](#get-form-elements)
- [`getPerPage`](#get-per-page)
- [`search`](#search)
- [`sort`](#sort)
- [`whereStatus`](#where-status)
- [`storeFiles`](#store-files)
- [`storeImages`](#store-images)
- [`macro`](#macro)
- [`__call`](#call)

<a name="set-decorators"></a>	
**`setDecorators()`**

The method accepts an array of [decorators](decorators.md) classes as the first argument, and can take a repository object as the second argument, instead of the default object from the `$this->repository` property:

```php	
$this->setDecorators([
    CharacterDecorator::class,
    CharacterCollectionDecorator::class
], $repository);
```

<a name="get-form-elements"></a>		
**`getFormElements()`**

The `getFormElements` method gets the form elements from the passed request object by [name of the form element prefix](forms.md#get-element-prefix) using the `getFormElements` macro for the `Illuminate\Http\Request` class.

The resulting array of form elements can be processed using the [`array_column`](https://www.php.net/manual/ru/function.array-column.php) function if you pass the second and third arguments, which will be passed along with an array into a function:

```php	
request()->all();

/*
	array:4 [
		"_token" => "vKT2Z9CuJEQx2jHl7XML9b0F3GSpKb9WhRdarw0S"
		"_method" => "POST"
		"element" => array:3 [
			3 => array:2 [
				"id" => "3"
				"name" => "Name 3"
			]
			5 => array:2 [
				  "id" => "5"
				  "name" => "Name 5"
			]
			7 => array:2 [
				"id" => "7"
				"name" => "Name 7"
			]
		]
	]
*/

//request()->get(FormDecorator::getElementsPrefixName());
request()->getFormElements();

/*
	array:3 [
		3 => array:2 []
		5 => array:2 []
		7 => array:2 []
	]
*/

$this->getFormElements('id');

/*
	array:3 [
		0 => "3"
		1 => "5"
		2 => "7"
	]
*/

```

<a name="get-per-page"></a>		
**`getPerPage()`**

By default, the number of items displayed on the page is detected by the value of the `perPage` query string argument in the HTTP request using the `getPerPage` method, if the request object is passed in it:

```php
// App\Services\Character\CharacterService
	
public function paginate(Request $request)
{
	return $this->repository->paginate($this->getPerPage($request));
}
```
	
If there is no query string argument `perpage` in the HTTP request or the request object is not passed to the `getPerpage` method, then the method will return the default number of elements `$this->perPage`, which can be set when creating the service object in the controller using the method `setPerpage()`:    

```php
// App\Http\Controllers\Character\CharacterController

public function __construct(CharacterService $characterService)
{
	$this->service = $characterService->setPerpage(25);
}
```

The static method `getPerPageName` returns the default name of the query string argument `perpage`, which can be changed using the static method
 `setPerPageName('perpage')`.  

<a name="search"></a>	    
**`search()`**

The `search` method calls the repository method to search based on the HTTP request:

```php
// ?search=text&search_by=id
$this->repository->searchById($request->search);
	
// ?search=text&search_by=name
$this->repository->search($request->search, $request->search_by);
	
// ?search=text
$this->repository->search($request->search);
```

> Note that the [repository method](repositories.md#call) will be called with the `searchBy` prefix and the name according to the value of the `search_by` argument in the HTTP request string, if such a repository method exists, otherwise the `search`, with the value of the argument `search_by`, if a column with that name exists.

By default, a search request is found by the value of the `search` argument in the HTTP request string using the `search` method, which takes a request object:

```php
// App\Services\Character\CharacterService

public function paginate(Request $request)
{
	$this->search($request);
    
	return $this->repository->paginate($this->getPerPage());
}
```
    
You can also handle the search request in the service layer by overriding the `prepareSearch` method of the base service class:

```php
// App\Services\Character\CharacterService
	
/**
 * Prepare the search query string value.
 *
 * @param string $value
 * @return string
 */
public function prepareSearch($value)
{
	return str_replace('/', '.', $value);
}
``` 

The static method `getSearchName` by default returns the name of the `search` argument in the HTTP request string, which can be changed using the static method method
 `setSearchName` or pass to the` search` method as the first argument instead of the request object:
 
```php
$this->search('match');
	
/*
	// ?match=text
	$this->repository->search($request->match);
*/	
```

The static method `getSearchByName` by default returns the name of the `search_by` argument in the HTTP request string, which can be changed using the static method
 `setSearchByName` or pass to the` search` method as the second argument:

```php
$this->search('match', 'match_by')	
	
/*
	// ?match=text&match_by=name
	$this->repository->matchByName($request->match);
*/
```

If you pass the third argument to the `search` method, then the name of the prefix of the repository method will be changed:

```php
$this->search('', '', 'match');
	
/*
	// ?search=text
	$this->repository->match($request->search);
*/
```

<a name="sort"></a>		
**`sort()`**

The `sort` method calls the repository method to sort based on the HTTP request: 

```php	
// ?sort=id&desc=1
$this->repository->sortById($request->desc);
	
// ?sort=name&desc=0
$this->repository->sort($request->desc, $request->name);
	
// ?sort=name
$this->repository->sortByName();
```
	
> Note that the [repository method](repositories.md#call) will be called with the `sortBy` prefix and named according to the value of the  `sort` argument in the HTTP request string, if such a repository method exists, otherwise the `sort` method will be called, with the value of the argument `sortBy`, if a column with that name exists.	

By default, a sort request is detected by the value of the `sort` argument in the HTTP request string using the `sort` method, which takes a request object:

```php
// App\Services\Character\CharacterService

public function paginate(Request $request)
{
	$this->sort($request);
    
	return $this->repository->paginate($this->getPerPage());
}
```	
	
The static `getSortingName` method by default returns the `sort` name of the argument in the HTTP request string, which can be changed with the static `setSortingName` method or passed to the `sort` method as the first argument instead of the request object:

```php
$this->sort('order');
	
/*
	// ?order=name
	$this->repository->sortByName();
*/
```
 
The static method `getSortingDescName` by default returns the name of the `desc` argument in the HTTP request string, which can be changed using the static method
 `setSortingDescName` or pass to the `sort` method as the second argument:

```php
$this->sort('order', 'direction');
	
/*
	// ?order=name&direction=1
	$this->repository->sortByName($request->direction);
*/
```
	
If you pass the third argument to the `sort` method, the name of the prefix of the repository method will be changed:

```php
$this->sort($request, '', 'order');
	
/*
	// ?order=name
	$this->repository->orderByName();
*/
```
	
If the first argument is not passed to the `sort` method, or a matching argument is found in the HTTP request string, then the repository method will be called for the default sort:

```php
$this->sort();

// $this->repository->sort();
``` 	
	
You can set default sorting values using the `setSorting` method, for example, when creating a service object in a controller:

```php
// App\Http\Controllers\Character\CharacterController

public function __construct(CharacterService $characterService)
{
    $this->service = $characterService
        ->setSorting('name', 'desc');
}
```    

<a name="where-status"></a>	
**`whereStatus()`**

The `whereStatus` method calls the repository method to compare the status depending on the HTTP request: 

```php
// ?status=closed
$this->repository->whereStatusClosed('=');
	
// ?status=1
$this->repository->whereStatus('=', 1);
```

> Note that the repository method will be called with the `whereStatus` prefix and the name according to the value of the `status` argument in the HTTP request string, if such a repository method exists, otherwise the `whereStatus` method will be called. 	

By default, a sort request is detected by the value of the `status` argument in the HTTP request string using the `whereStatus` method, which takes a request object:

```php
// App\Services\Character\CharacterService

public function paginate(Request $request)
{
	$this->whereStatus($request);
    
	return $this->repository->paginate($this->getPerPage());
}
```

The static `getStatusName` method by default returns the name of the `status` argument in the HTTP request string, which can be changed using the static `setStatusName` method or passed to the `whereStatus` method as the first argument instead of the request object:

```php
$this->whereStatus('st');
	
/*
	// ?st=opened
	$this->repository->whereStatusOpened('=');
*/
```
	
To set the default status values, you can pass the second arguments with the status value and the third argument with the comparison operator value to the `whereStatus` method:

```php
$this->whereStatus($request, '1', '=');
	
// $this->repository->whereStatus('=', 1);
```
	
If you pass the fourth argument to the `whereStatus` method, the name of the prefix of the repository method will be changed:

```php
$this->whereStatus($request, '', '', 'status');
	
/*
	// ?status=opened
	$this->repository->statusOpened();
*/
```	

You can set default status values, separately for regular users and authorized users, using the `setStatus` and `setStatusAdmin` methods, for example, when creating a service object in the controller:

```php
// App\Http\Controllers\Character\CharacterController

public function __construct(UserService $userService)
{
    $this->service = $userService
        ->setStatus('opened')
        ->setAdminStatus('archived', '!=');
}
    
// App\Services\Character\CharacterService

public function paginate(Request $request)
{
	$this->whereStatus();
    
	return $this->repository->paginate($this->getPerPage());
}
```
		
In this case, for ordinary users, using the `whereStatus` method, the repository method will be called to get items only with the "open" status:

```php
$this->repository->whereStatusOpened('=');
```

For authorized users, the repository method will be called to retrieve all items except for the "archived" status:

```php	
$this->repository->whereStatusArchived('!=');
```

> Note that in order to determine whether [the user is authorized to perform an action](auth.md#authorization), the name of the current route with the prefix `config('admin.prefix')` is passed to the gateway `Gate::allows('admin.character.show')`.

If the first argument is not passed to the `whereStatus` method, or a matching argument is not found in the HTTP request string, but the default status values are set, then the repository method will be called to compare the default status:

```php
$this->setStatus(0, '!=')->whereStatus();
	
// $this->repository->whereStatus(0, '!=');
```

<a name="store-files"></a>	
**`storeFiles()`**

The `storeFiles` method takes a decorator object using the [file upload trait](#uploading-files) and stores the uploaded files on the filesystem disk: 

```php
// App\Http\Controllers\Character\CharacterController

/**
 * Update the specified resource in the repository.
 *
 * @param int $id
 * @return \Illuminate\Http\RedirectResponse|string
 */
public function update($id)
{
	$item = $this->service->find($id);
	
	$item->getElements()->validate();
	
	$this->service->save($item);
	
	$this->service->storeFiles($item);
	
	return back();
}
```
	
> Note that if the second argument with the value `true` is not passed to the `storeFiles` method, the uploaded files will be saved if the call to the [`save`](repositories.md#save) method is successful.

<a name="store-images"></a>	
**`storeImages()`**

The `storeImages` method takes a decorator object using the [image upload trait](#uploading-images) and saves the uploaded images to the filesystem disk:

```php
// App\Http\Controllers\Character\CharacterController

/**
 * Update the specified resource in the repository.
 *
 * @param int $id
 * @return \Illuminate\Http\RedirectResponse|string
 */
public function update($id)
{
	$item = $this->service->find($id);
	
	$item->getElements()->validate();
	
	$this->service->save($item);
	
	$this->service->storeImages($item);
	
	return back();
}
```
	
> Note that if the second argument with the value `true` is not passed to the `storeImages` method, the uploaded files will be saved if the call to the [`save`](repositories.md#save) method is successful.

<a name="macro"></a>	
**`macro()`**

The service is "macro-implementable", which allows custom methods to be added to the base class of the service at runtime. For example, the following code adds a `whereOpened` method to the service class:

```php  
<?php
	
namespace App\Providers;
	
use Illuminate\Support\ServiceProvider;
use Laravelayers\Foundation\Services\Service;
	
class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		Service::macro('whereOpened', function() {
		    if (request()->has('open')) {
		        $this->repository->whereStatusOpened();
		    }
		});
    }
}
```    
	
<a name="call"></a>	    
**`__call()`**

If a non-existent service method is called, but such a public method exists in the repository, then the repository method will be called:

```php
// App\Http\Controllers\Character\CharacterController
	
/**
 * Remove the specified item from repository.
 *
 * @param int $id
 * @return \Illuminate\Http\RedirectResponse
 */
public function destroy($id)
{
    $this->service->destroy($id);
	
    return back();
}
	
// Laravelayers\Foundation\Repositories\Repository
	
/**
 * Destroy the models for the given IDs.
 *
 * @param array|int $ids
 * @return int
 */
public function destroy($ids)
{
    return $this->model->destroy($ids);
}
```
