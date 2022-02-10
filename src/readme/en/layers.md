# Layered structure

- [Introduction](#introduction)
- [Layers](#layers)
	- [Controllers](#controllers)
	- [Services](#services)
	- [Decorators](#decorators)
	- [Repositories](#repositories)
	- [Models](#models)
- [Defining classes](#defining-classes)

<a name="introduction"></a>
## Introduction

The implementation of the layered class structure is based on the idea  <https://www.toptal.com/php/maintain-slim-php-mvc-frameworks-with-a-layered-structure>.

The service is injected into the controller, into which the repository is injected, into which the model and the decorator are injected, with the help of which the data obtained from the model is transformed and returned in the decorator object.

**General UML Layered Structure Diagram**

[![General UML diagram of the layered structure](../../storage/images/general-uml-diagram-of-the-layered-structure.svg)](https://lucid.app/documents/view/16a364a8-19b7-4136-a555-02f58b0c696e)

**UML diagram of the sequence of interaction of layers**

[![UML diagram of the sequence](../../storage/images/uml-diagram-of-the-sequence.svg)
](https://lucid.app/documents/view/6c217ff3-3a7a-4806-bdf8-7a9b117c9de4)

<a name="layers"></a>
## Layers

<a name="controllers"></a>
### Controllers

[Controller](controllers.md) is a thin layer, it only receives a request and returns a response, so it can only call methods of the service layer and doesn't know anything about the repository. Below is the controller class using the service.

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

<a name="services"></a>	
### Services

[Service layer](services.md) is a layer of business logic, in which a request coming from a controller is processed, ie. conditions are used with the help of which the necessary methods of the repository are called.

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
        	->sorting($request->get('desc') ? 'desc' : 'asc')
        	->paginate();
    }
}
```

<a name="repositories"></a>
### Repositories

[Repository](repositories.md) calls the model's methods and returns the transformed data in the decorator object.

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
				$query->with('author')->Sorting();
			}])
		);
	}	    
}
```

<a name="decorators"></a>	
### Decorators

[Decorator](decorators.md) creates an object with additional methods for working with data received by the repository from the model and converted using the DTO class into a data array or data collection.

```php
<?php

namespace App\Decorators\Character;
	
use App\Decorators\Book\BookDecorator;
use Laravelayers\Foundation\Decorators\DataDecorator;
	
class CharacterDecorator extends DataDecorator
{
    /**
     * Get the name.
     *
     * @return string
     */	    
    function getName()
    {
        return strtoupper($this->get('name'));
    }
    
    /**
     * Get books.
     *
     * @return \Laravelayers\Foundation\Decorators\CollectionDecorator
     */	    
    function getBooks()
    {
        return BookDecorator::make($this->get('books', collect()));
    }		    
}
```

<a name="models"></a>
### Models

[Model](models.md) contains data about the database table and its relationships, as well as the query scopes.

```php
<?php
	
namespace App\Models\Character;
	
use App\Models\Book\Book;
use App\Models\Book\Book2Character;
use Laravelayers\Foundation\Models\Model;
	
class Character extends Model
{
	/**
	 * Define the relationship to the book.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function books()
	{
		return $this->hasManyThrough(
			Book::class,
			Book2Character::class,
			'character_id',
			'book_id',
			'id',
			'id'
		);
	} 
	   
	/**
	 * Search by default.
	 *
	 * @param $query
	 * @param $search
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeSearch($query, $search)
	{
	    return $query->whereKey($search);
	}
	
	/**
	 * Sorting by default.
	 *
	 * @param $query
	 * @param string $direction
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeSorting($query, $direction = 'desc')
	{
	    return $query->orderBy($this->getQualifiedKeyName(), $direction);
	}	
}
```
	
<a name="defining-classes"></a>	
## Defining classes

The easiest way to create all layers at once is [create a controller with an injected service layer](controllers.md#inject-service-layer) by executing the `make:controller` Artisan command with the`--service` option:

```php
php artisan make:controller Character/Character -s
```

UML class structure diagram:

[![UML diagram of class structure  in Laravelayers](../../storage/images/uml-diagram-of-class-structure.svg)](https://lucid.app/documents/view/4f287ac4-f718-4216-b508-105fa1b035f4)
