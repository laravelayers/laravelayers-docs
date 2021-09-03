# Декораторы

- [Введение](#introduction)
- [Базовый декоратор](#decorator)
- [Декоратор данных](#data-decorator)
- [Декоратор коллекции](#collection-decorator)
- [Декоратор Пагинатора](#paginator-decorator)
- [Определение декораторов](#defining-decorators)
	- [Дополнительные методы](#additional-methods)

<a name="introduction"></a>
## Введение

[Слой](layers.md) декоратора внедряется в репозиторий. По умолчанию, данные полученные репозиторием из модели передаются в базовый декоратор `Laravelayers\Foundation\Decorator`, включенный в Laravelayers. Декоратор использует класс `Laravelayers\Foundation\Dto` для преобразования данных в массив или коллекцию, и в зависимости от типа данных возвращает объект соответствующего декоратора.

**Общая UML диаграмма слоистой структуры**

[![General UML diagram of the layered structure](../../storage/images/general-uml-diagram-of-the-layered-structure.svg)](https://lucid.app/documents/view/16a364a8-19b7-4136-a555-02f58b0c696e)

**UML диаграмма последовательности взаимодействия слоев**

[![UML diagram of the sequence](../../storage/images/uml-diagram-of-the-sequence.svg)
](https://lucid.app/documents/view/6c217ff3-3a7a-4806-bdf8-7a9b117c9de4)

<a name="decorator"></a>
## Базовый декоратор

Все декораторы являются классами-потомками базового декоратора `Laravelayers\Foundation\Decorator`, в котором определены базовые методы для всех декораторов:

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

Статический метод `make` создает объект декоратора в соответствии с типом данных:

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

Метод `get` возвращает значение по заданному ключу. Если ключ не существует, возвращается `null`:

```php
$data->get('name');
	
// "Toto"
```
	
Если значение это массив, то возвращается объект декоратора данных:

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

Если значение это коллекция, то возвращается объект декоратора коллекции:

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
	
При желании вы можете передать значение по умолчанию в качестве второго аргумента:

```php
$data->get('surname', 'Gale');
	
// Gale
```
	
Если аргументы не указаны, то возвращаются данные декоратора:

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
	
Значение по заданному ключу можно получить динамически, если в объекте нет свойства с таким именем:

```php
$data->id;
//$data['id']
	
// 1
```

<a name="all"></a>
**`all()`**

Метод `all` так же как и метод `get` возвращает данные декоратора, но с декорированными значениями:

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

Метод `put` устанавливает заданный ключ и значение в  декораторе:

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

Метод `has` определяет, существует ли указанный ключ в  декораторе:

```php
$data->has('surname');
	
// true
```

<a name="forget"></a>
**`forget()`**

Метод `forget` удаляет элемент из декоратора по его ключу:

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

Метод `count` возвращает общее количество элементов в декораторе:

```php
$data->count();
	
// 4
```

<a name="isEmpty"></a>
**`isEmpty()`**

Метод `isEmpty` возвращает `true`, если декоратор пустой; в противном случае возвращается `false`:

```php
$data->isEmpty();
	
// false
```

<a name="isNotEmpty"></a>
**`isNotEmpty()`**

Метод `isNotEmpty` возвращает `true`, если декоратор не пустой; в противном случае возвращается `false`:

```php
$data->isNotEmpty();
	
// true
```

<a name="toArray"></a>
**`toArray()`**

Метод `toArray` преобразует данные декоратора в простой массив PHP.

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

Метод `toJson` преобразует данные декоратора в сериализованную строку JSON:

```php
$data->toJson();
	
// {"id":1,"name":"Toto","book":{...},"friends":[...]}
```

<a name="data-decorator"></a>
## Декоратор данных

Декоратор данных предназначен для объектов моделей и других данных преобразуемых в массив, кроме коллекций.

Все определяемые декораторы данных, должны расширяться от базового декоратора данных `Laravelayers\Foundation\DataDecorator`, в котором определены базовые методы для всех декораторов данных: 

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

Статический метод `make` создает объект декоратора данных, преобразованных в массив:

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
	
При декорировании данных модели, вы также можете получить значения аксессоров, для этого необходимо добавить имена атрибутов из методов аксессоров в свойство `$appends` модели, в результате значения аксессоров будут добавлены в массив данных декораторов:

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

Но чтобы значения аксессоров не смешивались с массивом данных, соответствующим оригинальным атрибутам модели, необходимо [установить собственный декоратор данных](#defining-decorators) для модели и добавить для него соответствующие имена свойств:

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

Метод `getTable` возвращает имя таблицы, полученное из объекта модели.

<a name="get-primary-key"></a>
**`getKey()`**

Метод `getKey` возвращает значение ключа данных, соответствующего именем первичного ключа, полученного с помощью метода `getKeyName`:

```php
$data->getKey();
	
// 1
```

<a name="get-primary-key"></a>
**`getKeyName()`**

Метод `getKeyName` возвращает имя первичного ключа, полученного из объекта модели или установленного с помощью метода `setKeyName`:

```php
$data->getKeyName();
	
// "id"
```

<a name="get-only-original"></a>
**`getOnlyOriginal()`**

Метод `getOnlyOriginal` возвращает все элементы массива, в соответствии с массивом оригинальных ключей, полученных с помощью метода [`getOriginalKeys`](#get-original-keys):

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
	
Если оригинальных ключей нет, то метод вернет все элементы массива, если не передать значение `false` в качестве первого аргумента. 
	
По умолчанию, метод возвращает элементы с не декорированными значениями так же как и при вызове метода [`get`](#get), но вы можете передать значение `true` в качестве второго аргумента, тогда данные будут декорированы так же как и при вызове метода [`all`](#all). 

<a name="get-original-keys"></a>
**`getOriginalKeys()`**

Метод `getOriginalKeys` возвращает оригинальные ключи элементов массива, полученные из ключей оригинальных атрибутов объекта модели:
	
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

Метод `syncOriginalKeys` синхронизирует оригинальные ключи с текущими ключами элементов массива.

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

Метод `getDateKeys` возвращает ключи элементов массива со значениями даты, полученные из объекта модели:

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

Метод `getTimestampKeys` возвращает ключи элементов массива только со значениями даты создания и обновления, полученные из столбцов `created_at` и `updated_at` объекта модели:

```php
$data->getTimestampKeys();

/*
	array:2 [
		"created_at" => "created_at"
		"updated_at" => "updated_at"
	]
*/
```
	
С помощью свойства `timestamps` объекта декоратора, вы можете изменить значение свойства `timestamps` объекта модели, перед [сохранением данных](repositories.md#save).

<a name="get-relation"></a>
**`getRelation()`**

Метод `getRelation` возвращает данные указанной связанной таблицы, полученные из загруженных отношений объекта модели или добавленные с помощью метода `setRelation`:

```php
$data->getRelation('book');
	
// DataDecorator {...}
	
$data->getRelation('test');
	
// null
	
$data->setRelation('test', [])->getRelation('test');
	
// DataDecorator {...}
```
	
> Обратите внимание, что если значение это массив, то возвращается объект декоратора данных, если коллекция, то возвращается объект декоратора коллекции, как и при вызове метода [`get`](#get).

При желании вы можете передать значение по умолчанию в качестве второго аргумента:

```php
$data->getRelation('tests', DataDecorator::make(collect()));
	
// CollectionDecorator {...}
```
	
Также, данные связанной таблицы можно получить динамически, если данные декоратора не содержат элемента с соответствующим ключом:

```php
$data->book;
	
// DataDecorator {...}
```
	
Также для связанной таблицы может быть определен метод с префиксом `get` для динамического получения данных:

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
    
Только с помощью метода `getRelation` можно изменить данные связанной таблицы в декораторе данных, если для нее был определен метод с префиксом `get` и именем, соответствующим имени таблицы:

```php
$this->getBook()->test;
	
// null

$this->getRelation('book')->put('test', 1);
	
$this->book->test;
	
// 1
```
	
Если метод с префиксом `get` возвращает данные в объекте нового декоратора, то для изменения данных с помощью метода нового декоратора, следует использовать метод `setRelation`:

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

Метод `getRelations()` возвращает массив с декорированными данными связанных таблиц, полученными из загруженных отношений объекта модели или добавленные с помощью метода `setRelations`:

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
	
Если передать в метод значение `false`, то будет возвращен массив с не декорированными данными связанных таблиц, если не использовался метод [`getRelation`](#get-relation).

<a name="get-hidden-keys"></a>
**`getHiddenKeys()`**

Метод `getHiddenKeys` возвращает ключи элементов массива скрытых для сериализации, полученные из объекта модели или установленные с помощью метода `setHiddenKeys()`:

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

Метод `getIsSelected` возвращает `true`, если значение элемента массива с ключом `isSelected` не пустое; в противном случае возвращается `false`:

```php
$data->isSelected; 
	
// null

$data->setIsSelected(true)->getIsSelected();
	
// true
```

<a name="collection-decorator"></a>
## Декоратор коллекции

Декоратор коллекции предназначен для объекта коллекции `Illuminate\Support\Collection`, поэтому объект декоратора коллекции позволяет использовать любой метод коллекции:

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

Все определяемые декораторы коллекции, должны расширяться от базового декоратора коллекции `Laravelayers\Foundation\CollectionDecorator`, в котором определены базовые методы для всех декораторов коллекции: 

- [`make`](#collection-make)
- [`getKeys`](#get-primary-keys)
- [`getByKey`](#get-by-primary-key)
- [`getExcept`](#get-except)
- [`getOnly`](#get-only)
- [`getSelectedItems`](#get-selected-items)

<a name="collection-make"></a>
**`make()`**

Статический метод `make` создает объект декоратора коллекции, если входящие данные являются объектом коллекции или могут быть преобразованы в коллекцию:

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
	
Чтобы декорировать каждый элемент коллекции, достаточно использовать декоратор данных для объекта коллекции, но также его можно использовать и для объекта декоратора коллекции:

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

По умолчанию, декоратор коллекции применяется к объекту `Illuminate\Database\Eloquent\Collection`, получаемого репозиторием из модели, а к каждой модели применяется декоратор данных:

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

Метод `getKeys` возвращает первичные ключи для всех элементов в коллекции:

```php
$items->getKeys();
	
// [2, 3]
```

<a name="get-by-primary-key"></a>
**`getByKey()`**	

Метод `getByKey` находит элемент с заданным первичным ключом. Если первый аргумент `$key` является экземпляром декоратора данных, тогда `getByKey` попытается вернуть элемент, соответствующий первичному ключу. Если `$key` является массивом ключей, тогда `getByKey` возвращает коллекцию всех элементов, которые соответствуют `$keys`, используя метод `whereIn` объекта коллекции:

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

Метод `getExcept` возвращает коллекцию всех элементов, которые не имеют указанных первичных ключей:	

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

Метод `getOnly` возвращает коллекцию всех элементов, которые имеют указанные первичные ключи:	

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

Метод `getSelectedItems` возвращает все элементы, для которых значение элемента массива с ключом `isSelected` не пустое:

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
	
Обратите внимание, что метод `setSelectedItems` принимает массив ИД элементов, для которых значение элемента массива с ключом `isSelected` будет установлено в `true`.

<a name="paginator-decorator"></a>
## Декоратор Пагинатора

Декоратор Пагинатора расширяется от базового декоратора коллекции и предназначен для объектов `Laravelayers\Pagination\Paginator` и `Laravelayers\Pagination\SimplePaginator`, включенных в Laravelayers, которые расширяют базовые классы `Illuminate\Pagination\LengthAwarePaginator` и `Illuminate\Pagination\Paginator`, включенные в Laravel. Поэтому декоратор позволяет использовать любой метод Пагинатора и коллекции.

По умолчанию представление `pagination::foundation` используется для отображения ссылок на страницы c помощью метода `links()`. Представление `pagination::summary` используется для отображения общего количества элементов и количества показанных элементов на странице с помощью метода `summary()`.

Чтобы кастомизировать представления разбивки на страницы - экспортируйте их в каталог `/resources/views/vendor` с помощью команды `vendor:publish`:

```php
php artisan vendor:publish --tag=laravelayers-pagination
```

<a name="defining-decorators"></a>
## Определение декораторов

Ниже приведен класс декоратора данных, с помощью которого определяется метод с префиксом `get` для получения значения по ключу объекта декоратора.

> Обратите внимание, что декоратор расширяет базовый класс декоратора данных, включенный в Laravelayers.

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
	
В результате динамического вызова	 свойства объекта декоратора или метода `getName`, будет возвращено декорированное значение:

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

Для получения оригинального значения следует использовать метод `get`:

```php
$data->get('name'); // "Toto" 
``` 
	
Также в декораторе данных можно установить псевдоним для заданного ключа элемента массива, с помощью имени метода с  префиксом `getAs`:

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
    
В таком случае при вызове элемента массива по ключу будет вызван его псевдоним:

```php
$data->get('nickname'); // "Toto"
	
$data->put('nickname', 'Totoshka');
	
$data->get('nickname'); // "Totoshka"
$data->get('name'); // "Totoshka"	
```

Декоратор используется также как и базовый декоратор:

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

Установить декоратор для данных, возвращаемых репозиторием, можно в [сервисном слое](services.md#inject-repository).
	
Простейший способ создать декоратор — выполнить Artisan-команду `make:decorator`:

```php
php artisan make:decorator Character/Character
```

В результате выполнения данной команды будет создан класс `App\Decorators\Character\CharacterDecorator`.

UML диаграмма структуры классов:

[![UML diagram of class structure  in Laravelayers](../../storage/images/uml-diagram-of-class-structure.svg)](https://lucid.app/documents/view/4f287ac4-f718-4216-b508-105fa1b035f4)

По умолчанию декоратора создаются в директории `app/Decorators`, но если при указании имени декоратора в начале указать слэш `/Character/Character`, то декоратор будет создан в директории приложения `app/Character/CharacterDecorator`.

Имя класса будет содержать постфикс соответствующий имени слоя `CharacterDecorator`. По умолчанию, имя класса, файла и директории будет преобразовано в строку СamelCase с большой буквой в начале. Для отмены преобразования следует использовать опцию `--nm`:

```php
php artisan make:decorator Character/Character --nm
```
	
Для создания декоратора коллекции следует использовать опцию `--collection`:	

```php
php artisan make:decorator Character/Character -c
```
	
Для изменения имени класса базового декоратора следует использовать опцию `--rp`:

```php
php artisan make:decorator Character/Character --rp App/Decorators/DataDecorator
```

Или переопределить вызов команды создания декоратора в сервис-провайдере:

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
	
Также можно настроить файлы заглушек, используемых при выполнении команды создания декоратора. Для этого следует выполнить команду публикации наиболее распространенных заглушек:

```php
php artisan stub:publish
```
	
В сервис-провайдере можно переопределить вызов класса `Laravelayers\Foundation\Dto` и классов декораторов, включенных в Laravelayers:

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
### Дополнительные методы

- [`getData`](#get-data)
- [`getDataKey`](#get-data-key)
- [`getRenderer`](#get-renderer)
- [`toArray`](#own-to-array)

<a name="get-data"></a>
**`getData()`**

Метод `getData` возвращает данные декоратора, в том числе данные вложенного декоратора:

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
	
Установить новые данные декоратора, в том числе данные вложенного декоратора, можно с помощью метода `setData`:

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

Метод `getDataKey` возвращает данные декоратора:

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
	
Установить новые данные декоратора, можно с помощью метода `setDataKey`:	

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

Метод `getRenderer` пробует вызывать метод с префиксом `render` для заданного ключа или возвращает значение по заданному ключу:

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
	
Возвращаемый результат будет преобразован в строку:

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
	
Метод `getRendererText` возвращает значение по заданному ключу преобразованное в текст (без html тегов). Для получения значения вызывается метод `getRenderer` в который передается второй аргумент `true`. Метод `getRenderer` пробует вызывать метод с префиксом `render` для заданного ключа и вторым аргументом `true`, если результатом является не строка, то возвращается значение по заданному ключу:

```php
<!-- Stored in resources/views/character/index.blade.php -->
	
$data->getRenderText('name');
	
// "Toto"
	
$data->getRenderText('friends');
	
// "Dorothy Gale, Scarecrow, Tin Woodman, Cowardly Lion"
```
	
Метод `getCroppedRenderText` возвращается значение по заданному ключу преобразованное в текст с помощью метода `getRendererText` и обрезает текст до указанной длинны:

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

Для декораторов можно добавлять свойства объекта доступные для сериализации:

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
		
Также можно добавлять методы объекта доступные для сериализации с помощью метода `setVisibleGetters` и получать их с помощью метода `getVisibleGetters`.
