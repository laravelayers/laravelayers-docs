# Decorators

- [Introduction](#introduction)
- [Basic decorator](#decorator)
- [Data decorator](#data-decorator)
- [Collection decorator](#collection-decorator)
- [Paginator Decorator](#paginator-decorator)
- [Defining decorators](#defining-decorators)
	- [Additional methods](#additional-methods)

<a name="introduction"></a>
## Introduction

The [layer](layers.md) of the decorator is injected into the repository. By default, the data retrieved by the repository from the model is passed to the base decorator `Laravelayers\Foundation\Decorator` included in Laravelayers. The decorator uses the `Laravelayers\Foundation\Dto` class to transform the data into an array or collection, and depending on the data type, returns the corresponding decorator object.

**General UML Layered Structure Diagram**

[![General UML diagram of the layered structure](../../storage/images/general-uml-diagram-of-the-layered-structure.svg)](https://lucid.app/documents/view/16a364a8-19b7-4136-a555-02f58b0c696e)

**UML diagram of the sequence of interaction of layers**

[![UML diagram of the sequence](../../storage/images/uml-diagram-of-the-sequence.svg)
](https://lucid.app/documents/view/6c217ff3-3a7a-4806-bdf8-7a9b117c9de4)

<a name="decorator"></a>
## Basic decorator

All decorators are descendant classes of the base `Laravelayers\Foundation\Decorator` decorator, which defines the base methods for all decorators:

- [`make`](#make)
- [`get`](#get)
- [`all`](#all)
- [`put`](#put)
- [`has`](#has)
- [`forget`](#forget)
- [`count`](#count)
- [`isEmpty`](#isEmpty)
- [`isNotEmpty`](#isNotEmpty)
- [`toArray`](#toArray)
- [`toJson`](#toJson)

<a name="make"></a>
**`make()`**

The static method `make` creates a decorator object according to the data type:

```php
$data = [
	'id' => 1, 
	'name' => 'Toto',
	'book' => [
		'title' => 'The Wonderful Wizard of Oz',
		'author' => 'L. Frank Baum',
		'year' => '1900'
	],
	'friends' => collect([
		'Dorothy Gale',
		'Scarecrow',
		'Tin Woodman',
		'Cowardly Lion'
	])
];

$data = Decorator::make($data);

/*
	DataDecorator {
	    #dataKey: "data"
	    #data: array:4 [
			"id" => 1
			"name" => "Toto"    
			"book" => array:3 [...]
			"friends" => Collection {
				#items: array:4 [...]
			}
	    ]
		...
	}
*/
```

<a name="get"></a>
**`get()`**

The `get` method returns the value for the given key. If the key does not exist, `null` is returned:

```php
$data->get('name');
	
// "Toto"
```
	
If the value is an array, then a data decorator object is returned:

```php	
$data->get('book');
	
/*
	DataDecorator {
		#dataKey: "data"
		#data: array:3 [
			"title" => "The Wonderful Wizard of Oz"
			"author" => "L. Frank Baum"
			"year" => "1900"
		]
		...
	}
*/
```

If the value is a collection, then a collection decorator object is returned:

```php
$data->get('friends');

/*
	CollectionDecorator {
		#dataKey: "items"
		#items: Collection {
			#items: array:4 [
				0 => "Dorothy Gale"
				1 => "Scarecrow"
				2 => "Tin Woodman"
				3 => "Cowardly Lion"
			]
		}
		...
	}
*/
```
	
Optionally, you can pass the default value as the second argument:

```php
$data->get('surname', 'Gale');
	
// Gale
```
	
If no arguments are specified, then the decorator data is returned:

```php
$data->get();

/*
	array:4 [
		"id" => 1
		"name" => "Toto"    
		"book" => array:3 [...]
		"friends" => Collection {...}
	]
*/
```
	
The value for the specified key can be obtained dynamically if the object does not have a property with the same name:

```php
$data->id;
//$data['id']
	
// 1
```

<a name="all"></a>
**`all()`**

The `all` method, just like the `get` method, returns the decorator data, but with decorated values:

```php
$data->all();

/*
	array:4 [
		"id" => 2
		"name" => "Toto"
		"book" => DataDecorator {...}
		"friends" => CollectionDecorator {...}
	]
*/
```
	
<a name="put"></a>	
**`put()`**

The `put` method sets the given key and value to  decorator:

```php
$data->put('surname', 'Gale');
	
$data->all();

/*
	array:4 [
		"id" => 2
		"name" => "Toto"
		"book" => DataDecorator {...}
		"friends" => CollectionDecorator {...}
		"surname" => "Gale"
	]
*/
```

<a name="has"></a>
**`has()`**

The `has` method determines if the specified key exists in  decorator:

```php
$data->has('surname');
	
// true
```

<a name="forget"></a>
**`forget()`**

The `forget` method removes an element from the decorator by its key:

```php
$data->forget('surname');
	
$data->all();

/*
	array:4 [
		"id" => 2
		"name" => "Toto"
		"book" => DataDecorator {...}
		"friends" => CollectionDecorator {...}
	]
*/
```

<a name="count"></a>
**`count()`**

The `count` method returns the total number of elements in the decorator:

```php
$data->count();
	
// 4
```

<a name="isEmpty"></a>
**`isEmpty()`**

The `isEmpty` method returns `true` if the decorator is empty; otherwise, it returns `false`:

```php
$data->isEmpty();
	
// false
```

<a name="isNotEmpty"></a>
**`isNotEmpty()`**

The `isNotEmpty` method returns `true` if the decorator is not empty; otherwise, it returns `false`:

```php
$data->isNotEmpty();
	
// true
```

<a name="toArray"></a>
**`toArray()`**

The `toArray` method converts the decorator data into a simple PHP array.

```php
$data->toArray();
	
/*
	array:4 [
		"id" => 1
		"name" => "Toto"
		"book" => array:3 [...]
		"friends" => array:4 [...]
	]
*/
```

<a name="toJson"></a>
**`toJson()`**

The `toJson` method converts the decorator data to a serialized JSON string:

```php
$data->toJson();
	
// {"id":1,"name":"Toto","book":{...},"friends":[...]}
```

<a name="data-decorator"></a>
## Data decorator

The data decorator is intended for model objects and other data converted to an array, except for collections.

All data decorators you define must extend from the base `Laravelayers\Foundation\DataDecorator` data decorator, which defines the base methods for all data decorators: 

- [`make`](#data-make)
- [`getTable`](#get-table-name)
- [`getKey`](#get-primary-key)
- [`getKeyName`](#get-primary-key-name)
- [`getOnlyOriginal`](#get-only-original)
- [`getOriginalKeys`](#get-original-keys)
- [`syncOriginalKeys`](#sync-original-keys)
- [`getDateKeys`](#get-date-keys)
- [`getTimestampKeys`](#get-timestamp-keys)
- [`getRelation`](#get-relation)
- [`getRelations`](#get-relations)
- [`getHiddenKeys`](#get-hidden-keys) 
- [`getIsSelected`](#get-is-selected)

<a name="data-make"></a>
**`make()`**

The static method `make` creates a decorator object for the data converted to an array:

```php		
$model = Character::find(1);

/*
	Character {
		#primaryKey: "id"
		#attributes: array:4 [
			"id" => 1
			"name" => "Toto"
			"created_at" => "2019-12-31 23:59:00"
			"updated_at" => "2020-02-29 00:01:00"    
	  	]
		#original: array:4 [...]
		#relations: array:2 [
			"book" => Book {...}
			"friends" => Friends {...}
		]
		#hidden: array:2 [
			0 => "created_at"
			1 => "updated_at"
		]  
		+timestamps: true
	}
*/
	
$data = Decorator::make($model);
//$data = DataDecorator::make($model);
	
/*
	DataDecorator {
		#dataKey: "data"
		#data: array:4 [
			"id" => 1
			"name" => "Toto"
			"created_at" => Carbon @ {...}
			"updated_at" => Carbon @ {...}
		]
		#table: "characters"
		#primaryKey: "id"
		#originalKeys: array:4 [
			0 => "id"
			1 => "name"
			2 => "created_at"
			3 => "updated_at"
		]
		#dateKeys: array:2 [
			0 => "created_at"
			1 => "updated_at"
		]
		#timestampKeys: array:2 [
			"created_at" => "created_at"
			"updated_at" => "updated_at"
		]
		#relations: array:2 [
			"book" => array:3 [...]
			"friends" => Collection {
				#items: array:4 [...]
			}
		]
		#hiddenKeys: array:2 [
			0 => "created_at"
			1 => "updated_at"
		]
		...
	}	
*/
```
	
When decorating the model data, you can also get the values of the accessors, for this you need to add the attribute names from the accessor methods to the `$appends` property of the model, as a result the values of the accessors will be added to the decorator data array:

```php
<?php

namespace App\Models\Character;
	
use Laravelayers\Foundation\Models\Model;
	
class Character extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character';
    
	/**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'table'
    ];
    
    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTableAttribute()
    {
        return $this->getTable();
    }
}

/*
	DataDecorator {
		#dataKey: "data"
		#data: array:4 [
			"id" => 1
			"name" => "Toto"
			"created_at" => Carbon @ {...}
			"updated_at" => Carbon @ {...}
			"table" => "character"
		]
		...
	}	
*/
```

But to prevent the accessor values from mixing with the data array corresponding to the original model attributes, you need to [set your own data decorator](#defining-decorators) for the model and add the corresponding property names for it:

```php
<?php

namespace App\Decorators\Character;
	
use App\Decorators\Book\BookDecorator;
use Laravelayers\Foundation\Decorators\DataDecorator;
	
class CharacterDecorator extends DataDecorator
{
	/**
	 * The table name.
	 *
	 * @var string
	 */
	protected $table;		    
}
	
/*
	DataDecorator {
		#table: "character"
		#dataKey: "data"
		#data: array:4 [
			"id" => 1
			"name" => "Toto"
			"created_at" => Carbon @ {...}
			"updated_at" => Carbon @ {...}
		]
		...
	}	
*/
```

<a name="get-table-name"></a>
**`getTable()`**

The `getTable` method returns the name of the table obtained from the model object.

<a name="get-primary-key"></a>
**`getKey()`**

The `getKey` method returns the value of the data key corresponding to the name of the primary key obtained using the` getKeyName` method:

```php
$data->getKey();
	
// 1
```

<a name="get-primary-key"></a>
**`getKeyName()`**

The `getKeyName` method returns the name of the primary key retrieved from the model object or set using the `setKeyName` method:

```php
$data->getKeyName();
	
// "id"
```

<a name="get-only-original"></a>
**`getOnlyOriginal()`**

The `getOnlyOriginal` method returns all the elements of the array, according to the array of original keys obtained using the [`getOriginalKeys`](#get-original-keys) method:

```php
$data->getOnlyOriginal();
	
/*
	array:4 [
		"id" => 1
		"name" => "Toto"
		"created_at" => Carbon @ {...}
		"updated_at" => Carbon @ {...}
	]
*/
```
	
If there are no original keys, then the method will return all the elements of the array, unless you pass the value `false` as the first argument. 
	
By default, the method returns elements with non-decorated values just like when calling the [`get`](#get) method, but you can pass the value `true` as the second argument, then the data will be decorated just like when called method [`all`](#all). 

<a name="get-original-keys"></a>
**`getOriginalKeys()`**

The `getOriginalKeys` method returns the original keys of the array elements obtained from the keys of the original attributes of the model object:
	
```php
$data->getOriginalKeys();
	
/*
	array:4 [
		0 => "id"
		1 => "name"
		2 => "created_at"
		3 => "updated_at"
	]
*/
```	

<a name="sync-original-keys"></a>
**`syncOriginalKeys()`**

The `syncOriginalKeys` method synchronizes the original keys with the current keys of the array elements.

```php
$data->put('surname', 'Gale')
	->syncOriginalKeys()
	->getOriginalKeys();
	
/*
	array:4 [
		0 => "id"
		1 => "name"
		2 => "created_at"
		3 => "updated_at"
		4 => "surname"
	]
*/	
```

<a name="get-date-keys()"></a>
**`getDateKeys()`**

The `getDateKeys` method returns the keys of the array elements with date values obtained from the model object:

```php
$data->getDateKeys();

/*
	array:2 [
		0 => "created_at"
		1 => "updated_at"
	]
*/
```

<a name="get-timestamp-keys()"></a>
**`getTimestampKeys()`**

The `getTimestampKeys` method returns the keys of the array elements only with the creation and update date values obtained from the `created_at` and `updated_at` columns of the model object:

```php
$data->getTimestampKeys();

/*
	array:2 [
		"created_at" => "created_at"
		"updated_at" => "updated_at"
	]
*/
```
	
Using the `timestamps` property of the decorator object, you can change the value of the`timestamps` property of the model object, before [saving data](repositories.md#save).

<a name="get-relation"></a>
**`getRelation()`**

The `getRelation` method returns the data for the specified related table, obtained from the loaded relations of the model object or added using the `setRelation` method:

```php
$data->getRelation('book');
	
// DataDecorator {...}
	
$data->getRelation('test');
	
// null
	
$data->setRelation('test', [])->getRelation('test');
	
// DataDecorator {...}
```
	
> Note that if the value is an array, then the data decorator object is returned, if it is a collection, then the collection decorator object is returned, as in the call to the [`get`](#get) method.

Optionally, you can pass the default value as the second argument:

```php
$data->getRelation('tests', DataDecorator::make(collect()));
	
// CollectionDecorator {...}
```
	
Also, the data of the linked table can be obtained dynamically if the decorator data does not contain an element with the corresponding key:

```php
$data->book;
	
// DataDecorator {...}
```
	
Also, for a linked table, a method with the `get` prefix can be defined to dynamically retrieve data:

```php
/**
 * Get the book.
 *
 * @return App\Decorators\Book\BookDecorator
 */
public function getBook()
{
    return BookDecorator::make($this->getRelation('book'));
}
    
$data->book;
//$data->getBook();
    
// BookDecorator {...}
```
    
Only using the `getRelation` method can the data of the linked table in the data decorator be changed if a method has been defined for it with the `get` prefix and the name corresponding to the table name:

```php
$this->getBook()->test;
	
// null

$this->getRelation('book')->put('test', 1);
	
$this->book->test;
	
// 1
```
	
If the method with the `get` prefix returns data in the object of the new decorator, then to change the data using the method of the new decorator, use the `setRelation` method:

```php
$data->setRelation('book, $data->book->setIsSelected(true));
	
$data->getBook();
//$data->getRelation('book');
	
/*
	BookDecorator {
		#dataKey: "data"
		#data: array:3 [
			"title" => "The Wonderful Wizard of Oz"
			"author" => "L. Frank Baum"
			"year" => "1900"
		]
		#isSelected: true
		...
	}
*/
```

<a name="get-relations"></a>
**`getRelations()`**

The `getRelations()` method returns an array with the decorated related table data obtained from the loaded relations of the model object or added using the `setRelations` method:

```php	
$data->setRelations([
	'book' => [],
	'friends' => collect()
]);
	
$data->getRelations();
	
/*
	array:2 [
		"book" => DataDecorator {...}
		"friends" => CollectionDecorator {...}
	]
*/
```
	
If you pass the value `false` to the method, then an array with unadorned data of related tables will be returned, if the [`getRelation`](#get-relation) method was not used.

<a name="get-hidden-keys"></a>
**`getHiddenKeys()`**

The `getHiddenKeys` method returns the keys of the array elements hidden for serialization, obtained from the model object or set using the`setHiddenKeys()`method:

```php
$data->getHiddenKeys();
	
/*
	array:2 [
		0 => "created_at"
		1 => "updated_at"
	]
*/
	
$data->toArray();

/*
	array:4 [
		"id" => 1
		"name" => "Toto"
		"surname" => "Gale"
	]
*/	
```

<a name="get-is-selected"></a>
**`getIsSelected()`**

The `getIsSelected` method returns `true` if the value of the array element with the `isSelected` key is not empty; otherwise, it returns `false`:

```php
$data->isSelected; 
	
// null

$data->setIsSelected(true)->getIsSelected();
	
// true
```

<a name="collection-decorator"></a>
## Collection decorator

The collection decorator is for the `Illuminate\Support\Collection` collection object, so the collection decorator object allows any collection method to be used:

```php
$friends = $data->get('friends');

/*
	CollectionDecorator {
		#dataKey: "items"
		#items: Collection {
			#items: array:4 [
				0 => "Dorothy Gale"
				1 => "Scarecrow"
				2 => "Tin Woodman"
				3 => "Cowardly Lion"
			]
		}
		...
	}
*/

$friends->implode(', ');
	
// "Dorothy Gale, Scarecrow, Tin Woodman, Cowardly Lion"
```

All collection decorators that you define must extend from the base collection decorator `Laravelayers\Foundation\CollectionDecorator`, which defines the base methods for all collection decorators: 

- [`make`](#collection-make)
- [`getKeys`](#get-primary-keys)
- [`getByKey`](#get-by-primary-key)
- [`getExcept`](#get-except)
- [`getOnly`](#get-only)
- [`getSelectedItems`](#get-selected-items)

<a name="collection-make"></a>
**`make()`**

The static method `make` creates a collection decorator object if the input is a collection object or can be converted to a collection:

```php
$items = [
	'Dorothy Gale',
	'Scarecrow',
	'Tin Woodman',
	'Cowardly Lion'
];

$items = CollectionDecorator::make($items);
	
/*
	CollectionDecorator {
	  #dataKey: "items"
	  #items: Collection {
	    #items: array:4 [
	      0 => "Dorothy Gale"
	      1 => "Scarecrow"
	      2 => "Tin Woodman"
	      3 => "Cowardly Lion"
	    ]
	  }
	}
*/
```
	
To decorate each item in a collection, it is sufficient to use the data decorator for the collection object, but it can also be used for the collection decorator object:

```php
$items = collect([
    0 => [
        'id' => 2,
        'name' => 'Dorothy Gale'
    ],
    1 => [
        'id' => 3,
        'name' => 'Scarecrow'
    ]
]);
    
$items = Decorator::make($items);

/*
	CollectionDecorator {
	  #dataKey: "items"
	  #items: Collection {
	    #items: array:2 [
	      0 => DataDecorator {
	        #dataKey: "data"
	        #data: array:2 [
	          "id" => 2
	          "name" => "Dorothy Gale"
	        ]
	      }
	      1 => DataDecorator {
	        #dataKey: "data"
	        #data: array:4 [
	          "id" => 3
	          "name" => "Scarecrow"
	          }
	        ]
	      }
	    ]
	  }
	}	
*/
```

By default, a collection decorator is applied to the `Illuminate\Database\Eloquent\Collection` object retrieved by the repository from a model, and a data decorator is applied to each model:

```php
$items = Character::findMany([2,3]);

/*
	Collection {
		#items: array:2 [	
			0 => Character {
				#primaryKey: "id"
				#attributes: array:4 [
					"id" => 2
					"name" => "Dorothy Gale"
					"created_at" => "2019-12-31 23:59:15"
					"updated_at" => "2020-02-29 00:01:15"    
			  	]
				#original: array:4 [...]
				#relations: array:2 [
					"book" => Book {...}
					"friends" => Friends {...}
				]
				#hidden: array:2 [
					0 => "created_at"
					1 => "updated_at"
				]  
				+timestamps: true
			}
			1 => Character {
				#primaryKey: "id"
				#attributes: array:4 [
					"id" => 3
					"name" => "Scarecrow"
					"created_at" => "2019-12-31 23:59:30"
					"updated_at" => "2020-02-29 00:01:30"    
			  	]
				#original: array:4 [...]
				#relations: array:2 [
					"book" => Book {...}
					"friends" => Friends {...}
				]
				#hidden: array:2 [
					0 => "created_at"
					1 => "updated_at"
				]  
				+timestamps: true
			}
		]
	}				
*/
	
$items = Decorator::make($items);

/*
	CollectionDecorator {
		#dataKey: "items"
		#items: Collection {
			#items: array:2 [
				0 => DataDecorator {
					#dataKey: "data"
					#data: array:4 [
						"id" => 2
						"name" => "Dorothy Gale"
						"created_at" => Carbon @ {...}
						"updated_at" => Carbon @ {...}  
					]
					#table: "characters"
					#primaryKey: "id"
					#originalKeys: array:4 [...]
					#dateKeys: array:2 [...]
					#timestampKeys: array:2 [...]
					#relations: array:2 [...]
					#hiddenKeys: array:2 [...]
				}
				1 => DataDecorator {
					#dataKey: "data"
					#data: array:4 [
						"id" => 3
						"name" => "Scarecrow"
						"created_at" => Carbon @ {...}
						"updated_at" => Carbon @ {...} 
					]
					#table: "characters"
					#primaryKey: "id"
					#originalKeys: array:4 [...]
					#dateKeys: array:2 [...]
					#timestampKeys: array:2 [...]
					#relations: array:2 [...]
					#hiddenKeys: array:2 [...]
				}
			]
		}
	}	
*/
```
	
<a name="get-primary-keys"></a>
**`getKeys()`**	

The `getKeys` method returns the primary keys for all items in the collection:

```php
$items->getKeys();
	
// [2, 3]
```

<a name="get-by-primary-key"></a>
**`getByKey()`**	

The `getByKey` method finds the element with the given primary key. If the first argument to `$key` is an instance of a data decorator, then `getByKey` will try to return the element corresponding to the primary key. If `$key` is an array of keys, then `getByKey` returns a collection of all the elements that match `$keys` using the `whereIn` method of the collection object:

```php
$items->getKey(2);
	
/*
	DataDecorator {
		#dataKey: "data"
		#data: array:6 [
			"id" => 2
			...
		]
		#table: "characters"
		#primaryKey: "id"
		...
	}
*/
```
	
<a name="get-except"></a>
**`getExcept()`**	

The `getExcept` method returns a collection of all elements that do not have the specified primary keys:	

```php
$items->getExcept([3]);
	
/*
	Collection {
		#items: array:1 [
			0 => DataDecorator {
				#dataKey: "data"
				#data: array:6 [
					"id" => 2
					...
				]
				#table: "characters"
				#primaryKey: "id"
				...
			}
		]
	}
*/
```

<a name="get-only"></a>
**`getOnly()`**	

The `getOnly` method returns a collection of all elements that have the specified primary keys:	

```php
$items->getOnly([2]);
	
/*
	Collection {
		#items: array:1 [...]
	}
*/
```

<a name="get-selected-items"></a>
**`getSelectedItems()`**

The `getSelectedItems` method returns all items for which the value of the array item with the `isSelected` key is not empty:

```php
$items->getSelectedItems()->count(); 
	
// 0
	
$items->setSelectedItems([2])
	->getByKey(2)
	->getIsSelected();
	
// true
	
$items->getSelectedItems();
	
/*
	Collection {
		#items: array:1 [
			0 => DataDecorator {
				#dataKey: "data"
				#data: array:7 [
					"id" => 2
					"isSelected" => true,
					...
				]
				#table: "characters"
				#primaryKey: "id"
				...
			}
		]
	}
*/
```
	
Note that the `setSelectedItems` method accepts an array of item IDs for which the value of the array item with the key `isSelected` will be set to `true`.

<a name="paginator-decorator"></a>
## Decorator Paginator

The Paginator decorator extends from the base collection decorator and targets the `Laravelayers\Pagination\Paginator` and `Laravelayers\Pagination\SimplePaginator` objects included in Laravelayers that extend the base classes `Illuminate\Pagination\LengthAwarePaginator` and `Illuminate\Pagination\Paginator` included in Laravel. Therefore, the decorator allows you to use any Paginator and collection method.

By default, the `pagination::foundation` view is used to display links to pages using the`links()` method. The `pagination::summary` view is used to display the total number of items and the number of items shown on a page using the `summary()` method.

To customize the pagination views, export them to the `/resources/views/vendor` directory using the `vendor:publish` command:

```php
php artisan vendor:publish --tag=laravelayers-pagination
```

<a name="defining-decorators"></a>
## Defining decorators

Below is a data decorator class that defines a method prefixed with `get` to get a value by the key of the decorator object.

> Note that the decorator extends the base data decorator class included with Laravelayers.

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
        return BookDecorator::make($this->getRelation('books') ?: collect());
    }		    
}
```
	
As a result of a dynamic call	 properties of the decorator object or the `getName` method, the decorated value will be returned:

```php
$data->name; // "TOTO"
$data->getName(); // "TOTO"
	
$data->getBooks();
	
/*
	CollectionDecorator {
		#dataKey: "items"
		#items: Collection {
			#items: array:2 [
				0 => BookDecorator {
					#dataKey: "data"
					#data: array:3 [
						"title" => "The Wonderful Wizard of Oz"
						"author" => "L. Frank Baum"
						"year" => "1900"
					]
					...
				}
				1 => BookDecorator {...}
			]
		}
	}
*/
```

To get the original value, use the `get` method:

```php
$data->get('name'); // "Toto" 
``` 
	
Also in the data decorator, you can set an alias for a given key of an array element using the method name with  prefix `getAs`:

```php
/**
 * Get the nickname alias key.
 *
 * @return string
 */	    
function getAsNickname()
{
    return 'name';
} 
```  
    
In this case, when calling an array element by key, its alias will be called:

```php
$data->get('nickname'); // "Toto"
	
$data->put('nickname', 'Totoshka');
	
$data->get('nickname'); // "Totoshka"
$data->get('name'); // "Totoshka"	
```

The decorator is used in the same way as the base decorator:

```php
$model = Character::find(1);

/*
	Character {
		#primaryKey: "id"
		#attributes: array:4 [...]
		...
	}
*/
	
$data = CharacterDecorator::make($model);
	
/*
	CharacterDecorator {
		#dataKey: "data"
		#data: array:6 [...]
		...
	}	
*/
```

You can set a decorator for the data returned by the repository in the [service layer](services.md#inject-repository).
	
The easiest way to create a decorator is to run the `make: decorator` Artisan command:

```php
php artisan make:decorator Character/Character
```

As a result of this command, the class `App\Decorators\Character\CharacterDecorator` will be created.

UML class structure diagram:

[![UML diagram of class structure  in Laravelayers](../../storage/images/uml-diagram-of-class-structure.svg)](https://lucid.app/documents/view/4f287ac4-f718-4216-b508-105fa1b035f4)

By default, decorators are created in the `app/Decorators` directory, but if you specify a slash `/Character/Character` at the beginning of the decorator name, the decorator will be created in the application's `app/Character/CharacterDecorator` directory.

The class name will contain a postfix corresponding to the layer name `CharacterDecorator`. By default, the name of the class, file and directory will be converted to a CamelCase string with a capital letter at the beginning. To cancel conversion, use the `--nm` option:

```php
php artisan make:decorator Character/Character --nm
```
	
To create a collection decorator, use the `--collection` option:	

```php
php artisan make:decorator Character/Character -c
```
	
To change the class name of the base decorator, use the `--rp` option:

```php
php artisan make:decorator Character/Character --rp App/Decorators/DataDecorator
```

Or, override the call to the command to create a decorator in the service provider:

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
        $this->app->extend('command.decorator.make', function () {
            return $this->app
                ->make(\Laravelayers\Foundation\Console\Commands\DecoratorMakeCommand::class)
            ->setBaseClass('App/Decorators/DataDecorator')
            ->setBaseCollectionClass('App/Decorators/CollectionDecorator');
        });
    }
}
```
	
You can also customize the stub files used when you run the create decorator command. To do this, run the command to publish the most common stubs:

```php
php artisan stub:publish
```
	
In the service provider, you can override the call to the `Laravelayers\Foundation\Dto` class and the decorator classes included in Laravelayers:

```php
<?php
	
namespace App\Providers;
	
use Illuminate\Support\ServiceProvider;
use Laravelayers\Foundation\Decorators\CollectionDecorator;
use Laravelayers\Foundation\Decorators\DataDecorator;
use Laravelayers\Foundation\Dto\Dto;
use Laravelayers\Pagination\Decorators\PaginatorDecorator;
	
class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(Dto::class, function ($app, $params) {
		    return App\Dto\Dto::make(...$params);
		});
			
		$this->app->bind(DataDecorator::class, function ($app, $params) {
		    return App\Decorators\DataDecorator::make(...$params);
		});
			
		$this->app->bind(CollectionDecorator::class, function ($app, $params) {
		    return App\Decorators\CollectionDecorator::make(...$params);
		});
		        
		$this->app->bind(PaginatorDecorator::class, function ($app, $params) {
		    return App\Decorators\PaginatorDecorator::make(...$params);
		}); 	        
	}
}
```

<a name="additional-methods"></a>
### Additional methods

- [`getData`](#get-data)
- [`getDataKey`](#get-data-key)
- [`getRenderer`](#get-renderer)
- [`toArray`](#own-to-array)

<a name="get-data"></a>
**`getData()`**

The `getData` method returns the decorator data, including the nested decorator data:

```php
$data = AdminCharacterDecorator::make($data);

/*
	AdminCharacterDecorator {
		#dataKey: "data"
		#data: CharacterDecorator {
			#dataKey: "data"
			#data: array:6 [
				"id" => 1
				"name" => "Toto"
				"created_at" => Carbon @ {...}
				"updated_at" => Carbon @ {...}  
			]
			...
		}
	}
*/
	
$data->getData();
	
/*
	array:6 [
		"id" => 1
		"name" => "Toto"
		"created_at" => Carbon @ {...}
		"updated_at" => Carbon @ {...} 
	]
*/
```
	
You can set new decorator data, including nested decorator data, using the `setData` method:

```php
$test = (clone $data)->setData(['test' => true]);

/*
	AdminCharacterDecorator {
		#dataKey: "data"
		#data: CharacterDecorator {
			#dataKey: "data"
			#data: array:1 [
				"test" => true
			]
			...
		}
	}
*/  
```   

<a name="get-data-key"></a>
**`getDataKey()`**

The `getDataKey` method returns the decorator data:

```php
$test->getDataKey();
	
/*
	CharacterDecorator {
		#dataKey: "data"
		#data: array:1 [
			"test" => true
		]
		...
	}		
*/
```
	
You can set new decorator data using the `setDataKey` method:	

```php
$test->setData(['test' => true]);

/*
	AdminCharacterDecorator {
		#dataKey: "data"
		#data: array:1 [
			"test" => true
		]
		...
	}
*/
```

<a name="get-renderer"></a>
**`getRenderer()`**

The `getRenderer` method tries to call a method with the `render` prefix on a given key, or returns a value on a given key:

```php
<?php

namespace App\Decorators\Character;
	
use Laravelayers\Foundation\Decorators\DataDecorator;
	
class CharacterDecorator extends DataDecorator
{
    /**
     * Render the name.
     *
     * @return \Illuminate\View\View
     */	    
    function renderName()
    {	    
		return view('foundation::layouts.a', [
			'slot' => $this->get('name'),
			'href' => route('character.show', ['id' => $this->id])
    }
		    
	/**
	 * Render friends.
	 *
	 * @param bool $text
	 * @return \Illuminate\View\View|string
	 */
	function renderFriends($text = false)
	{
	    if ($text) {
	        return $this->get('friends')->implode(', ');
	    }
	
	    return view('foundation::layouts.ul', [
	        'slot' => $this->get('friends')->toArray()
	    ]);
	}	    
}
```
	
The returned result will be converted to a string:

```php
<!-- Stored in resources/views/character/index.blade.php -->

$data->getRender('name');
	
// "<a href="/character/1">Toto</a>"
	
$data->getRender('friends');
	
/*
	<ul>
		<li>Dorothy Gale</li>
		<li>Scarecrow</li>
		<li>Tin Woodman</li>
		<li>Cowardly Lion</li>
	</ul>
*/
```	
	
The `getRendererText` method returns the value by the given key converted to text (without html tags). To get the value, the `getRenderer` method is called in which the second argument `true` is passed. The `getRenderer` method tries to call the method with the `render` prefix for the specified key and the second argument `true`, if the result is not a string, then the value for the specified key is returned:

```php
<!-- Stored in resources/views/character/index.blade.php -->
	
$data->getRenderText('name');
	
// "Toto"
	
$data->getRenderText('friends');
	
// "Dorothy Gale, Scarecrow, Tin Woodman, Cowardly Lion"
```
	
The `getCroppedRenderText` method returns the value by the specified key converted to text using the `getRendererText` method and trims the text to the specified length:

```php
<!-- Stored in resources/views/character/index.blade.php -->

$data->getCroppedRenderText('friends', 10);
	
// "Dorothy Gale,"
	
$data->getCroppedRenderText('friends', 10, true);
	
// " Scarecrow, Tin Woodman, Cowardly Lion"
	
$data->getCroppedRenderText('friends', 10, false, ',');
	
// "Dorothy Gale"
```	

<a name="own-to-array"></a>
**`toArray()`**

For decorators, you can add object properties available for serialization:

```php
$data->test = ['result' => true];

/*
	CharacterDecorator {
		#dataKey: "data"
		#data: array:6 [...]
		+"test": DataDecorator {
			#dataKey: "data"
			#data: array:1 [
			  "result" => true
			]
		}
		...
	}		
*/
	
$data->setVisibleProperties('test')->getVisibleProperties();	
	
/*
	array:1 [
		0 => "test"
	]
*/
	
$data->toArray();
	
/*
	array:2 [
		"test" => array:1 [
			"result" => true
		]
		"data" => array:4 [...]
	]
*/
```
		
You can also add object methods available for serialization using the `setVisibleGetters` method and get them using the `getVisibleGetters` method.
