# Сервисный слой

- [Введение](#introduction)
- [Определение сервисов](#defining-services)
	- [Внедрение репозитория](#inject-repository)
- [Базовый сервис](#base-service)	

<a name="introduction"></a>
## Введение

Сервисный [cлой](layers.md) внедряется в контроллер, обрабатывает поступающие от контроллера запросы, т.е. использует условия с помощью которых вызываются необходимые методы внедренного репозитория, который возвращает преобразованные данные в объекте декоратора.

**Общая UML диаграмма слоистой структуры**

[![General UML diagram of the layered structure](../../storage/images/general-uml-diagram-of-the-layered-structure.svg)](https://lucid.app/documents/view/16a364a8-19b7-4136-a555-02f58b0c696e)

**UML диаграмма последовательности взаимодействия слоев**

[![UML diagram of the sequence](../../storage/images/uml-diagram-of-the-sequence.svg)
](https://lucid.app/documents/view/6c217ff3-3a7a-4806-bdf8-7a9b117c9de4)

<a name="defining-services"></a>
## Определение сервисов

Ниже приведен класс сервиса с внедренным [репозиторием](repositories.md). Также в сервисе устанавливается декоратор с помощью метода [`setDecorators`](#set-decorators).

> Обратите внимание, что сервис расширяет базовый класс сервиса, включенный в Laravelayers.

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
### Внедрение репозитория
	
Простейший способ создать сервис с внедренным репозиторием и декоратором для репозитория — выполнить Artisan-команду `make:service` с опциями `--repository` и `--decorator`:

```php
php artisan make:service Character/Character -r -d
```

В результате выполнения данной команды будут созданы следующие классы:

- Сервис `App\Services\Character\CharacterService`.
- Репозиторий `App\Repositories\Character\CharacterRepository`.
- Декоратор `App\Decorators\Character\CharacterDecorator`.
- Модель `App\Models\Character\Character`.

UML диаграмма структуры классов:

[![UML diagram of class structure  in Laravelayers](../../storage/images/uml-diagram-of-class-structure.svg)](https://lucid.app/documents/view/4f287ac4-f718-4216-b508-105fa1b035f4)

Указанная поддиректория `Character`, будет создана в директории для каждого слоя.

По умолчанию сервисы создаются в директории `app/Services`, но если при указании имени сервиса в начале указать слэш `/Character/Character`, то сервис будет создан в директории приложения `app/Character/CharacterService`.

Имена классов, кроме модели будут содержать постфикс соответствующий имени слоя `CharacterService`. По умолчанию, все имена классов, файлов и директорий будут преобразованы в строку СamelCase с большой буквой в начале. Для отмены преобразования следует использовать опцию `--nm`:

```php
php artisan make:service Character/Character --repository --decorator --nm
```

Для изменения имени класса репозитория следует использовать опцию `--rn`:

```php
php artisan make:service Character/Character --repository --decorator --rn Book/Character
```
	
Для изменения имени класса декоратора следует использовать опцию `--dn`:

```php
php artisan make:service Character/Character --repository --decorator --dn Book/Character
```

Для создания декоратора коллекции следует использовать опцию `--collection`:	

```php
php artisan make:service Character/Character --repository -c
```

Для изменения имени класса базового сервиса следует использовать опцию `--rp`:

```php
php artisan make:service -s Character/Character --rp App/Services/BaseService
```

Или переопределить вызов команды создания сервиса в сервис-провайдере:

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
	
Также можно настроить файлы заглушек, используемых при выполнении команды создания сервисного слоя. Для этого следует выполнить команду публикации наиболее распространенных заглушек:

```php
php artisan stub:publish
```

<a name="base-service"></a>
## Базовый сервис

Базовый класс сервиса `Laravelayers\Foundation\Services\Service` определят частное свойство `repository` для объекта репозитория и дополнительные методы:

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

Метод принимает в качестве первого аргумента массив классов [декораторов](decorators.md), в качестве второго аргумента может принимать объект репозитория, вместо объекта используемого по умолчанию из свойства `$this->repository`:

```php	
$this->setDecorators([
    CharacterDecorator::class,
    CharacterCollectionDecorator::class
], $repository);
```

<a name="get-form-elements"></a>		
**`getFormElements()`**

Метод `getFormElements` получает элементы формы из переданного объекта запроса по [имени префикса элементов формы](forms.md#get-element-prefix) с помощью макроса `getFormElements` для класса `Illuminate\Http\Request`.

Полученный массив элементов формы можно обработать с помощью функции [`array_column`](https://www.php.net/manual/ru/function.array-column.php), если передать второй и третий аргументы, которые будут переданы вместе с массивом в функцию:

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

По умолчанию, количество элементов отображаемых на странице обнаруживается по значению аргумента строки запроса `perPage` в HTTP-запросе с помощью метода `getPerPage`, если в нем передан объект запроса:

```php
// App\Services\Character\CharacterService
	
public function paginate(Request $request)
{
	return $this->repository->paginate($this->getPerPage($request));
}
```
	
Если аргумента строки запроса `perpage` в HTTP-запросе нет или объект запроса не передан в метод `getPerpage`, то метод вернет количество элементов по умолчанию `$this->perPage`, которое можно установить при создании объекта сервиса в контроллере с помощью метода `setPerpage()`:    

```php
// App\Http\Controllers\Character\CharacterController

public function __construct(CharacterService $characterService)
{
	$this->service = $characterService->setPerpage(25);
}
```

Статический метод `getPerPageName` возвращает имя аргумента строки запроса `perpage` по умолчанию, которое можно изменить с помощью статического метода
 `setPerPageName('perpage')`.  

<a name="search"></a>	    
**`search()`**

Метод `search` вызывает метод репозитория для поиска в зависимости от HTTP-запроса:

```php
// ?search=text&search_by=id
$this->repository->searchById($request->search);
	
// ?search=text&search_by=name
$this->repository->search($request->search, $request->search_by);
	
// ?search=text
$this->repository->search($request->search);
```

> Обратите внимание, что будет вызван [метод репозитория](repositories.md#call) c префиксом `searchBy` и названием в соответствии со значением аргумента `search_by` в строке HTTP-запроса, если такой метод репозитория существует, иначе будет вызван метод `search`, со значением аргумента `search_by`, если столбец с таким именем существует.

По умолчанию поисковый запрос обнаруживается по значению аргумента `search` в строке HTTP-запроса с помощью метода `search`, который принимает объект запроса:

```php
// App\Services\Character\CharacterService

public function paginate(Request $request)
{
	$this->search($request);
    
	return $this->repository->paginate($this->getPerPage());
}
```
    
Вы также можете обработать поисковый запрос в сервисном слое, переопределив метод `prepareSearch` базового класса сервиса:

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

Статический метод `getSearchName` по умолчанию возвращает имя `search` аргумента в строке HTTP-запроса, которое можно изменить с помощью статического метода
 `setSearchName` или передать в метод `search` в качестве первого аргумента вместо объекта запроса:
 
```php
$this->search('match');
	
/*
	// ?match=text
	$this->repository->search($request->match);
*/	
```

Статический метод `getSearchByName` по умолчанию возвращает имя `search_by` аргумента в строке HTTP-запроса, которое можно изменить с помощью статического метода
 `setSearchByName` или передать в метод `search` в качестве второго аргумента:

```php
$this->search('match', 'match_by')	
	
/*
	// ?match=text&match_by=name
	$this->repository->matchByName($request->match);
*/
```

Если передать в метод `search` третий аргумент, то будет изменено имя префикса метода репозитория:

```php
$this->search('', '', 'match');
	
/*
	// ?search=text
	$this->repository->match($request->search);
*/
```

<a name="sort"></a>		
**`sort()`**

Метод `sort` вызывает метод репозитория для сортировки в зависимости от HTTP-запроса: 

```php	
// ?sort=id&desc=1
$this->repository->sortById($request->desc);
	
// ?sort=name&desc=0
$this->repository->sort($request->desc, $request->name);
	
// ?sort=name
$this->repository->sortByName();
```
	
> Обратите внимание, что будет вызван [метод репозитория](repositories.md#call) c префиксом `sortBy` и названием в соответствии со значением аргумента `sort` в строке HTTP-запроса, если такой метод репозитория существует, иначе будет вызван метод `sort`, со значением аргумента `sortBy`, если столбец с таким именем существует.	

По умолчанию запрос сортировки обнаруживается по значению аргумента `sort` в строке HTTP-запросе с помощью метода `sort`, который принимает объект запроса:

```php
// App\Services\Character\CharacterService

public function paginate(Request $request)
{
	$this->sort($request);
    
	return $this->repository->paginate($this->getPerPage());
}
```	
	
Статический метод `getSortingName` по умолчанию возвращает имя `sort` аргумента в строке HTTP-запроса, которое можно изменить с помощью статического метода `setSortingName` или передать в метод `sort` в качестве первого аргумента вместо объекта запроса:

```php
$this->sort('order');
	
/*
	// ?order=name
	$this->repository->sortByName();
*/
```
 
Статический метод `getSortingDescName` по умолчанию возвращает имя `desc` аргумента в строке HTTP-запроса, которое можно изменить с помощью статического метода
 `setSortingDescName` или передать в метод `sort` в качестве второго аргумента:

```php
$this->sort('order', 'direction');
	
/*
	// ?order=name&direction=1
	$this->repository->sortByName($request->direction);
*/
```
	
Если передать в метод `sort` третий аргумент, то будет изменено имя префикса метода репозитория:

```php
$this->sort($request, '', 'order');
	
/*
	// ?order=name
	$this->repository->orderByName();
*/
```
	
Если первый аргумент не передан в метод `sort` или не обнаружен соответствующий аргумент в строке HTTP-запроса, то будет вызван метод репозитория для сортировки по умолчанию:

```php
$this->sort();

// $this->repository->sort();
``` 	
	
Установить значения сортировки по умолчанию, можно с помощью метода `setSorting`, например, при создании объекта сервиса в контроллере:

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

Метод `whereStatus` вызывает метод репозитория для сравнения статуса в зависимости от HTTP-запроса: 

```php
// ?status=closed
$this->repository->whereStatusClosed('=');
	
// ?status=1
$this->repository->whereStatus('=', 1);
```

> Обратите внимание, что будет вызван метод репозитория c префиксом `whereStatus` и названием в соответствии со значением аргумента `status` в строке HTTP-запроса, если такой метод репозитория существует, иначе будет вызван метод `whereStatus`. 	

По умолчанию запрос сортировки обнаруживается по значению аргумента `status` в строке HTTP-запросе с помощью метода `whereStatus`, который принимает объект запроса:

```php
// App\Services\Character\CharacterService

public function paginate(Request $request)
{
	$this->whereStatus($request);
    
	return $this->repository->paginate($this->getPerPage());
}
```

Статический метод `getStatusName` по умолчанию возвращает имя `status` аргумента в строке HTTP-запроса, которое можно изменить с помощью статического метода `setStatusName` или передать в метод `whereStatus` в качестве первого аргумента вместо объекта запроса:

```php
$this->whereStatus('st');
	
/*
	// ?st=opened
	$this->repository->whereStatusOpened('=');
*/
```
	
Установить значения статуса по умолчанию, можно передав в метод `whereStatus` второй аргументы со значением статуса и третий аргумент со значением оператора сравнения:

```php
$this->whereStatus($request, '1', '=');
	
// $this->repository->whereStatus('=', 1);
```
	
Если передать в метод `whereStatus` четвертый аргумент, то будет изменено имя префикса метода репозитория:

```php
$this->whereStatus($request, '', '', 'status');
	
/*
	// ?status=opened
	$this->repository->statusOpened();
*/
```	

Установить значения статуса по умолчанию, отдельно для обычных пользователей и авторизованных, можно с помощью методов `setStatus` и `setStatusAdmin`, например, при создании объекта сервиса в контроллере:

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
		
В таком случае для обычных пользователей, с помощью метода `whereStatus`, будет вызван метод репозитория для получения элементов только со статусом "открытый":

```php
$this->repository->whereStatusOpened('=');
```

Для авторизованных пользователей будет вызван метод репозитория для получения всех элементов кроме статуса "архивный":

```php	
$this->repository->whereStatusArchived('!=');
```

> Обратите внимание, что для того чтобы определить, [авторизован ли пользователь для выполнения действия](auth.md#authorization), имя текущего маршрута с префиксом `config('admin.prefix')` передается в шлюз `Gate::allows('admin.character.show')`.

Если первый аргумент не передан в метод `whereStatus` или не обнаружен соответствующий аргумент в строке HTTP-запроса, но установлено значения статуса по умолчанию, то будет вызван метод репозитория для сравнения статуса по умолчанию:

```php
$this->setStatus(0, '!=')->whereStatus();
	
// $this->repository->whereStatus(0, '!=');
```

<a name="store-files"></a>	
**`storeFiles()`**

Метод `storeFiles` принимает объект декоратора, использующего [трейт для загрузки файлов](#uploading-files), и сохраняет загруженные файлы на диске файловой системы: 

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
	
> Обратите внимание, что если в метод `storeFiles` не будет передан второй аргумент со значением `true`, то загруженные файлы будут сохранены, если вызов метода [`save`](repositories.md#save) будет выполнен успешно.

<a name="store-images"></a>	
**`storeImages()`**

Метод `storeImages` принимает объект декоратора, использующего [трейт для загрузки изображений](#uploading-images), и сохраняет загруженные изображения на диске файловой системы:

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
	
> Обратите внимание, что если в метод `storeImages` не будет передан второй аргумент со значением `true`, то загруженные файлы будут сохранены, если вызов метода [`save`](repositories.md#save) будет выполнен успешно.

<a name="macro"></a>	
**`macro()`**

Сервис является «макрореализуемыми», что позволяет добавлять пользовательские методы в базовый класс сервиса во время выполнения. Например, следующий код добавляет метод `whereOpened` в класс сервиса:

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

Если вызывается несуществующий метод сервиса, но такой публичный метод существует в репозитории, то будет вызван метод репозитория:

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
