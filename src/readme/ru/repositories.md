# Репозитории

- [Введение](#introduction)
- [Определение репозитория](#defining-repository)
	- [Внедрение модели](#inject-model)
- [Базовый репозиторий](#base-repository)

<a name="introduction"></a>
## Введение

[Слой](layers.md) репозиторий внедряется в сервисный слой, вызывает методы внедренной модели и возвращает преобразованные данные в объекте декоратора.

**Общая UML диаграмма слоистой структуры**

[![General UML diagram of the layered structure](../../storage/images/general-uml-diagram-of-the-layered-structure.svg)](https://lucid.app/documents/view/16a364a8-19b7-4136-a555-02f58b0c696e)

**UML диаграмма последовательности взаимодействия слоев**

[![UML diagram of the sequence](../../storage/images/uml-diagram-of-the-sequence.svg)
](https://lucid.app/documents/view/6c217ff3-3a7a-4806-bdf8-7a9b117c9de4)

<a name="defining-repository"></a>
## Определение репозитория

Ниже приведен класс репозитория с внедренной [моделью](models.md). 

> Обратите внимание, что репозиторий расширяет базовый класс репозитория, включенный в Laravelayers.

```php
<?php
	
namespace App\Repositories\Character;
	
use App\Models\Book\Character;
use Laravelayers\Foundation\Repositories\Repository;
	
class CharacterRepository extends Repository
{
    /**
     * Create a new CharacterRepository instance.
     *
     * @param Character $character
     */
    public function __construct(Character $character)
    {
        $this->model = $character;
    }
    
	/**
	 * Loading books for the character.
	 *
	 * @return $this
	 */
	public function withBooks()
	{
		return $this->query(
			$this->model->with(['books' => function($query) {
				$query->with('author')->sort();
			}])
		);
	}    
}
```

<a name="inject-model"></a>	
### Внедрение модели
	
Простейший способ создать репозиторий с внедренной моделью — выполнить Artisan-команду `make:repository` с опцией `--model`:

```php
php artisan make:repository Character/Character -m
```

В результате выполнения данной команды будут созданы следующие классы:

- Репозиторий `App\Repositories\Character\CharacterRepository`.
- Модель `App\Models\Character\Character`.

UML диаграмма структуры классов:

[![UML diagram of class structure  in Laravelayers](../../storage/images/uml-diagram-of-class-structure.svg)](https://lucid.app/documents/view/4f287ac4-f718-4216-b508-105fa1b035f4)

Указанная поддиректория `Character`, будет создана в директории для каждого слоя.

По умолчанию репозитории создаются в директории `app/Repositories`, но если при указании имени репозитория в начале указать слэш `/Character/Character`, то репозиторий будет создан в директории приложения `app/Character/CharacterRepository`.

Имя класса репозитория будет содержать постфикс соответствующий имени слоя `CharacterRepository`. По умолчанию, все имена классов, файлов и директорий будут преобразованы в строку СamelCase с большой буквой в начале. Для отмены преобразования следует использовать опцию `--nm`:

```php
php artisan make:repository Character/Character --model --nm
```

Для изменения имени класса модели следует использовать опцию `--mn`:

```php
php artisan make:repository Character/Character --model --mn Book/Character
```
	
Для изменения имени класса базового репозитория следует использовать опцию `--rp`:

```php
php artisan make:repository Character/Character --rp App/Repository/Repository
```

Или переопределить вызов команды создания репозитория в сервис-провайдере:

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
        $this->app->extend('command.repository.make', function () {
            return $this->app
                ->make(\Laravelayers\Foundation\Console\Commands\RepositoryMakeCommand::class)
                ->setBaseClass('App/Repositories/Repository');
        });
    }
}
```
	
Также можно настроить файлы заглушек, используемых при выполнении команды создания репозитория. Для этого следует выполнить команду публикации наиболее распространенных заглушек:

```php
php artisan stub:publish
```

<a name="base-repository"></a>
## Базовый репозиторий

Базовый класс репозитория `Laravelayers\Foundation\Repositories\Repository` определят частное свойство `model` для объекта модели и дополнительные методы для работы с репозиторием:

- [fill](#fill)
- [find](#find)
- [first](#first) 
- [paginate](#paginate)
- [get](#get)
- [count](#count)
- [exist](#exist)
- [doesntExist](#doesnt-exist)
- [save](#save)
- [destroy](#destroy)
- [query](#query)
- [decorate](#decorate)
- [macro](#macro)
- [__call](#call)

<a name="fill"></a>		
**`fill()`**

Метод `fill` получает массив с именами столбцов для таблицы модели, заполненый пустыми значениями и преобразованный в объект декоратора данных с помощью метода [`decorate`](#decorate). Ниже приведен код сервисного слоя, в котором вызывается этот метод и передается массив названий отношений, для которых также будет получен массив с именами столбцов:

```php
// App\Services\Character\CharacterService;

/**
 * Fill the resource instance with values.
 *
 * @return \App\Decorators\Character\CharacterDecorator
 */
public function fill()
{
    return $this->repository->fill(['books.author'], ['books.characters']);
}
	
/*
	CharacterDecorator {
		#dataKey: "data"
		#data: array:5 [
			"id" => null
			"name" => null
			"created_at" => null
			"updated_at" => null 
			"books" => array:9 [...]
		]						
		#primaryKey: "id"
		#originalKeys: array:4 [...]
		#dateKeys: array:2 [...]
		#timestampKeys: array:2 [...]
		#relationKeys: array:1 [...]
		#hiddenKeys: []
	}	
*/
```
	
> Обратите внимание, что можно получать данные для вложенных отношений, используя синтаксис "точка". Также вы можете передать в качестве второго аргумента массив значений по умолчанию, в том числе для отношений.

Дополнительный метод `fillWithTypes` добавляет в объект декоратора свойство `types`, которое содержит типы столбцов таблицы в БД, полученных с помощью метода [`getColumnTypes`](models.md#get-column-types) модели. Также метод получает типы столбцов для переданного массива названий связанных таблиц.
	
<a name="find"></a>		
**`find()`**

Метод `find` получает результат запроса по указанному первичному ключу, преобразованный в объект декоратора с помощью метода [`decorate`](#decorate):

```php
// Laravelayers\Foundation\Repositories\Repository
	
/**
 * Find a model by its primary key and make it.
 *
 * @param mixed $id
 * @param array $columns
 * @return \Laravelayers\Foundation\Decorators\DataDecorator
 */
public function find($id, $columns = ['*'])
{
    return $this->decorate(
        $this->model->find($id, $columns)
    );
}
```
	
Метод `findOrFail` в отличии от метода `find()` выдает исключение, если результат не найден:

```php
// Laravelayers\Foundation\Repositories\Repository
	
/**
 * Find a model by its primary key and make it or throw an exception.
 *
 * @param mixed $id
 * @param array $columns
 * @return \Laravelayers\Foundation\Decorators\DataDecorator
 *
 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
 */
public function findOrFail($id, $columns = ['*'])
{
    return $this->decorate(
        $this->model->findOrFail($id, $columns)
    );
}
```
	
Ниже приведен код сервисного слоя, в котором репозиторий используется как [построитель запроса](#query):

```php
// App\Services\Character\CharacterService;
	
/**
 * Find the repository item by the specified ID.
 *
 * @param int $id
 * @return \App\Decorators\Character\CharacterDecorator
 */
public function find($id)
{			
	return $this->repository
		->withBooks()
		->findOrFail($id);
}
	
/*
	CharacterDecorator {
		#dataKey: "data"
		#data: array:6 [
			"id" => 1
			"name" => "Toto"
			"created_at" => Carbon @ {...}
			"updated_at" => Carbon @ {...}  
			"books" => Collection {...}
			"friends" => Collection {...}
		]					
		#primaryKey: "id"
		#originalKeys: array:4 [...]
		#dateKeys: array:2 [...]
		#timestampKeys: array:2 [...]
		#relationKeys: array:2 [...]
		#hiddenKeys: []
	}	
*/
```

<a name="first"></a>		
**`first`**

Метод `first()` получает результат запроса, преобразованный в объект декоратора с помощью метода [`decorate`](#decorate), для первой найденной записи:

```php
// Laravelayers\Foundation\Repositories\Repository
	
/**
 * Execute the query, get the first result and make it.
 *
 * @param array $columns
 * @return \Laravelayers\Foundation\Decorators\DataDecorator
 */
public function first($columns = ['*'])
{
    return $this->decorate(
        $this->model->first($columns)
    );
}
```
	
Метод `firstOrFail()` в отличии от метода `first()` выдает исключение, если результат не найден:

```php
// Laravelayers\Foundation\Repositories\Repository
	
/**
 * Execute the query, get the first result and make it or throw an exception.
 *
 * @param array $columns
 * @return \Laravelayers\Foundation\Decorators\DataDecorator
 *
 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
 */
public function firstOrFail($columns = ['*'])
{
    return $this->decorate(
        $this->model->firstOrFail($columns)
    );
}
```

<a name="paginate"></a>	
**`paginate()`**

Метод `paginate` получает результат запроса c помощью метода [`PaginateManually`](models.md#scope-paginate-manually) базовой модели Laravelayers, преобразованный в объект декоратора с помощью метода [`decorate`](#decorate), для постраничного отображения элементов, c вычислением общего количества элементов и страниц, в том числе для запросов, которые содержат оператор `distinct` или `groupBy`.

Метод `simplePaginate` получает результат запроса c помощью метода [`SimplePaginateManually`](models.md#scope-simple-paginate-manually) базовой модели Laravelayers, преобразованного в объект декоратора с помощью метода [`decorate`](#decorate), для постраничного отображения элементов, с вычислением только наличия предыдущей и следующей страниц:

Ниже приведен код сервисного слоя, в котором репозиторий используется как [построитель запроса](#query) и методы репозитория вызываются в зависимости от условий, что исключает использование условий в репозитории:

```php
// App\Services\Character\CharacterService;
	
/**
 * Paginate repository items.
 *
 * @param Request $request
 * @return PaginatorDecorator
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

/*  
	PaginatorDecorator {
		#dataKey: "items"
		#items: Paginator {
			#total: 151
			#lastPage: 7
			#items: Collection {
				#items: array:25 [
					0 => CharacterDecorator {...}
					1 => CharacterDecorator {...}
					2 => CharacterDecorator {...}
					...
				]
			}
			#perPage: 25
			#currentPage: 1
			#path: "/"
			#query: []
			#fragment: null
			#pageName: "page"
		}
	}	     
*/
```

<a name="get"></a>		
**`get()`**

Метод `get` получает результат запроса, преобразованный в объект декоратора с помощью метода [`decorate`](#decorate).

```php
// Laravelayers\Foundation\Repositories\Repository
	
/**
 * Execute the query as a "select" statement and make it.
 *
 * @param array $columns
 * @return CollectionDecorator
 */
public function get($columns = ['*'])
{
    return $this->decorate(
        $this->model->get($columns)
    );
}
```

Ниже приведен код сервисного слоя, в котором репозиторий используется как [построитель запроса](#query):

```php
// App\Services\Character\CharacterService;
	
/**
 * Get repository items.
 *
 * @return CollectionDecorator
 */
public function get()
{			
	return $this->repository
		->withBooks()
		->get();
}
	
/*
    CollectionDecorator {
      #dataKey: "items"
      #items: Collection {
        #items: array:151 [
          0 => CharacterDecorator {...}
          1 => CharacterDecorator {...}
          2 => CharacterDecorator {...}
          ...
        ]
      }
    }   
*/
```

<a name="count"></a>		
**`count()`**

Метод `count` получает количество записей для указанных столбцов, соответствующих запросу, с помощью метода [`DisctinctCount`](models.md#scope-disctinct-count) базовой модели Laravelayers, в том числе для запросов, которые содержат оператор `distinct` или `groupBy`.

```php
/**
 * Retrieve the "count" result of the query.
 *
 * @param  string  $columns
 * @return int
 */
public function count($columns = '*')
{
    $result = $this->model->distinctCount($columns);

    $this->model = $this->model->getModel();

    return $result;
}
```

<a name="exists"></a>		
**`exists()`**

Метод `exists` возвращает `true`, если существуют какие-либо записи, соответствующие запросу.

<a name="doesnt-exist"></a>		
**`doesntExist()`**

Метод `doesntExist` возвращает `true`, если отсутствуют какие-либо записи, соответствующие запросу.

<a name="save"></a>
**`save()`**

Метод `save` принимает объект декоратора, из которого получает массив данных c помощью метода [`get`](decorators.md#get), заполняет ими модель с помощью метода `foreFill` и создает новую запись в базе данных или обновляет модель:

```php
/**
 * Save the model to the database.
 *
 * @param DataDecorator $item
 * @return DataDecorator|CollectionDecorator|PaginatorDecorator
 */
public function save(DataDecorator $item)
{
	$result = $this->result($item);
	
	$timestamps = $result->timestamps;
	
	$this->result($item)->timestamps = $item->timestamps;
	
	$saved = $result->forcefill($item->get())->save();
	
	$result->timestamps = $timestamps;
	
	return $this->decorate($saved ? $this->result() : []);
}
```
	
> Обратите внимание, что в объекте декоратора будут обновлены только те данные из формы, которые вы инициализируете с помощью [декоратора формы](forms.md). С помощью публичного свойства `timestamps` объекта декоратора, вы можете изменить значение свойства `timestamps` объекта модели, перед сохранением данных.

Для создания новой записи, сначала следует использовать метод [`fill`](#fill) в [сервисном слое](services.md#inject-repository), с помощью которого будет получен объект декоратора данных, содержащий массив с оригинальными атрибутами модели и пустыми значениями:

```php
// App\Services\Character\CharacterService

$item = $this->repository->fill();
	
$item->put('name', 'Scarecrow');
	
$result = $this->repository->save($item);

$result->isNotEmpty();

// true
```
	
При обновлении записи, сначала необходимо получить данные, в результате полученная модель или коллекция моделей будет сохранена в свойстве `result`, затем передать измененные данные декоратора в метод репозитория `save`:

```php
// App\Services\Character\CharacterService

$item = $this->repository->find(1);
	
$item->put('name', 'Totoshka');
	
$this->repository->save($item);
```
	
Для получения результата запроса в методе `save` вызывается метод `result`, в который передается полученный объект декоратора. Если результат запроса в свойстве `result` содержит коллекцию моделей, то метод `result()` вернет из коллекции модель, соответствующую первичному ключу объекта декоратора, и метод `save` обновит модель:

```php
$items = $this->repository->find([1,2,3]);	
$item = $item->first()->put('name', 'Totoshka');
	
$this->repository->save($item);
```
	
> Обратите внимание, что метод `getResult` репозитория, позволяет получить сохраненный результат запроса, преобразованный в объект декоратора с помощью метода [`decorate`](#decorate). 

Если вам нужно обновить связанные модели, то вы можете переопределить метод `save` в репозитории:

```php
<?php
	
namespace App\Repositories\Character;
	
use App\Models\Book\Character;
use Laravelayers\Foundation\Repositories\Repository;
	
class CharacterRepository extends Repository
{
	/**
	 * Create a new CharacterRepository instance.
	 *
	 * @param Character $character
	 */
	public function __construct(Character $character)
	{
		$this->model = $character;
	}
	
    /**
     * Save the model to the database.
     *
     * @param DataDecorator|ProductDecorator $item
     * @return \Laravelayers\Foundation\Decorators\DataDecorator
     */
    public function save(DataDecorator $item)
    {
        DB::transaction(function() use($item) {
            parent::save($item);
	
            $saved = $this->result($item)
                ->book()
                ->save(
                    $this->result($item)
                        ->books
                        ->forcefill($item->book->get())
                );
                
            foreach($item->friends as $key => $friend) {
                $saved = $this->result($item)
                    ->friends
                    ->get($key)
                    ->forcefill($friend->get())
                    ->save();
            }                
        });
	
        return $this->decorate($saved ? $this->result() : []);
    }    
}
```
	
> Обратите внимание, что при обновлении связанных моделей, используются [транзакции базы данных](#https://laravel.com/docs/database#database-transactions).

Вы также можете обновлять данные в других внедренных репозиториях:

```php
class CharacterRepository extends Repository
{
	/**
	 * Create a new CharacterRepository instance.
	 *
	 * @param Character $character
	 */
	public function __construct(Character $character, CharacterImage $characterImage)
	{
		$this->model = $character;
		$this->characterImage = $characterImage;
	}
	
    /**
     * Save the model to the database.
     *
     * @param DataDecorator|ProductDecorator $item
     * @return \Laravelayers\Foundation\Decorators\DataDecorator
     */
    public function save(DataDecorator $item)
    {
        DB::transaction(function() use($item) {
            $saved = parent::save($item);
            
            foreach($item->images as $image) {
                $saved = $this->characterImage->forcefill($image->get())->save();
            }
                           
        });
	
        return $this->decorate($saved ? $this->result() : []);
    }    
}
```

<a name="destroy"></a>	
**`destroy()`**

Метод `destroy` удаляет модель по указанному первичному ключу или массиву ключей:

```php
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
	
> Чтобы удалить данные из связанных таблиц вы можете добавить для них [внешние ключи](https://laravel.com/docs/migrations#foreign-key-constraints) или использовать [события Eloquent](https://laravel.com/docs/eloquent#events).	
	
<a name="query"></a>		
**`query()`**
	
Частный метод `query` принимает объект построителя запроса Eloquent, сохраняет его свойстве `model` и возвращает объект репозитория `$this`.

```php
// Laravelayers\Foundation\Repositories\Repository

/**
 * Set the query for the model.
 *
 * @param \Illuminate\Database\Eloquent\Builder $query
 * @return $this
 */
protected function query(Builder $query)
{
    $this->model = $query;
	
    return $this;
}
```
	
> Метод `query` предназначен для всех методов репозитория, которые используются для построения запроса.

Например, следующий код определяет метод репозитория для добавления ограничения в запрос:

```php
// App\Repositories\Character\CharacterRepository
	
/**
 * Add a where clause on the primary key to the query.
 *
 * @param mixed $id
 * @return $this
 */
public function whereKeyNot($id)
{
	return $this->query(
		$this->model->whereKeyNot($id)
	);
}
```
	
<a name="decorate"></a>		
**`decorate()`**

Частный метод `decorate` принимает результата запроса и возвращает объект [декоратора](decorators.md) с преобразованными данными в массив или коллекцию. При этом результат запроса сохраняется в приватном свойстве `result`, а экземпляр модели - в свойстве `model`.

Устанавливать декораторы для элементов и коллекции элементов, возвращаемых репозиторием, следует в [сервисном слое](services.md#inject-repository).

> Метод `decorate` предназначен для всех методов репозитория, которые возвращают результат запроса.

<a name="macro"></a>	
**`macro()`**

Репозиторий являются «макрореализуемыми», что позволяет добавлять пользовательские методы в базовый класс репозитория во время выполнения. Например, следующий код добавляет метод `all` в класс репозитория:

```php  
<?php
	
namespace App\Providers;
	
use Illuminate\Support\ServiceProvider;
use Laravelayers\Foundation\Repositories\Repository;
	
class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		Repository::macro('all', function() {
			return $this->decorate(
			    $this->model->all()
			);
		});
	}
}
```
	
<a name="call"></a>	    
**`__call()`**

Если вызывается несуществующий метод репозитория, но такой публичный метод с префиксом `scope` существует в модели, то будет вызван метод модели:

```php
// App\Services\Character\CharacterService;
	
/**
 * Paginate repository items.
 *
 * @param Request $request
 * @return PaginatorDecorator
 */
public function paginate(Request $request)
{		
	return $this->repository
    	->search($request)
    	->sort($request)
    	->paginate();
}
	
// App\Models\Character\Character;
	
/**
 * Search by default.
 *
 * @param \Illuminate\Database\Eloquent\Builder $query
 * @param string $search
 * @param string|null $column
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeSearch($query, $search, $column = null)
{
	return $column
		? $query->where($column, 'like', "{$search}%")
		: $query->whereKey($search);
}
	
/**
 * Sort by default.
 *
 * @param \Illuminate\Database\Eloquent\Builder $query
 * @param string $direction
 * @param string|null $column
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeSort($query, $direction = 'desc', $column = null)
{
	return $query->orderBy($column ?: $this->getQualifiedKeyName(), $direction);
}
```
	
> Использование заготовок запросов в моделях, с помощью методов с префиксом `scope`, исключает указание названия столбцов таблицы БД в репозиториях.

Если вызывается несуществующий метод репозитория с префиксом `with`, `withCount`, `has` или `doesntHave`, то будет вызван соответствующий метод модели, использующий название метода в качестве передаваемого параметра:

```php
// App\Services\Character\CharacterService;
	
/**
 * Get repository items.
 *
 * @return CollectionDecorator
 */
public function get()
{			
	return $this->repository
		->withCountBooks()
		->get();
}
	
// App\Repositories\Character\CharacterRepository
	
/*
	public function withCountBooks()
	{
	    return $this->query(
	        $this->model->withCount('books')
	    );
	}
*/
```
