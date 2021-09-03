# Навигация 

- [Введение](#introduction)
- [Декоратора меню](#menu-decorator)
- [Определение декоратора элемента меню](#defining-menu-item-decorator)
	- [Базовый декоратор элемента меню](#basic-menu-item-decorator)
	- [Трейт элемента меню](#menu-item-trait)
- [Построение дерева](#building-tree)
- [Отображение меню](#menu-display)

<a name="introduction"></a>
## Введение

Для построения дерева и отображения навигации предназначены декоратор меню и декоратор элемента меню.

![Laravelayers navigation menu](../../storage/images/navigation-menu.png)

<a name="menu-decorator"></a>
## Декоратора меню

Декоратор меню `Laravelayers\Navigation\Decorators\MenuDecorator` предназначен для коллекции элементов, где каждый элемент является [декоратором элемента меню](#defining-menu-item-decorator).

В [сервисном слое](services.md#inject-repository) вы можете установить декоратор меню для коллекции данных, возвращаемых репозиторием, и декоратор элемента меню:

```php
// App\Services\Article\CategoryService

/**
 * Create a new CategoryService instance.
 *
 * @param \App\Repositories\Article\CategoryRepository $categoryRepository
 */
public function __construct(CategoryRepository $categoryRepository)
{
	$this->repository = $categoryRepository;

	$this->setDecorators([
		MenuDecorator::class,
		CategoryDecorator::class,
	]);
}
```

<a name="defining-menu-item-decorator"></a>
## Определение декоратора элемента меню

В [декораторе данных](decorators.md#defining-decorators) должен быть реализован интерфейс элемента меню и узла дерева `Laravelayers\Contracts\Navigation\MenuItem`, использован [трейт элемента меню и узла дерева](#menu-item-trait) и определены дополнительные методы для реализации интерфейса:

```php
<?php
	
namespace App\Decorators\Article\Category;
	
use Laravelayers\Contracts\Navigation\MenuItem as MenuItemContract;
use Laravelayers\Foundation\Decorators\DataDecorator;
use Laravelayers\Navigation\Decorators\MenuItem;
	
class CategoryDecorator extends DataDecorator implements MenuItemContract
{
    use MenuItem;

	/**
	 * Get the menu item name.
	 *
	 * @return string
	 */
	public function getMenuName()
	{
		return $this->name;
	}
	
	/**
	 * Get the menu item url.
	 *
	 * @return string
	 */
	public function getMenuUrl()
	{
		return return route('category.show', ['id' => $this->getKey()]);
	}
	
	/**
	 * Get the value of the HTML attribute of the class of the menu item icon.
	 *
	 * @return string
	 */
	public function getMenuIcon()
	{
		return $this->category_icon ?: '';
	}
	
	/**
	 * Get the sort value of the menu item.
	 *
	 * @return int
	 */
	public function getMenuSorting()
	{
		return $this->categoryTree->sorting ?? 0;
	}
	
	/**
	 * Get the value of the HTML attribute of the class of the menu item.
	 *
	 * @return string
	 */
	public function getMenuClass()
	{
		return $this->get('status') === 0 ? 'is-status-closed' : '';
	}
	
	/**
	 * Get the parent menu item ID.
	 *
	 * @return int|string
	 */
	public function getMenuParentId()
	{
		return $this->categoryTree->parent_id ?? 0;
	}     
}
```

<a name="get-menu-name"></a>
**`getMenuName()`**

Метод `getMenuName` возвращает имя элемента меню, отображаемого в виде текста.

<a name="get-menu-url"></a>
**`getMenuUrl()`**

Метод `getMenuUrl` возвращает URL элемента меню.

<a name="get-menu-icon"></a>
**`getMenuIcon()`**

Метод `getMenuIcon` возвращает значение HTML атрибута класса для [иконки](frontend.md#icons) элемента меню.

<a name="get-menu-sorting"></a>
**`getMenuSorting()`**

Метод `getMenuSorting` возвращает порядок сортировки по умолчанию для элемента меню.

<a name="get-menu-class"></a>
**`getMenuClass()`**

Метод `getClass` возвращает значение HTML атрибута класса для элемента меню.

<a name="get-menu-parent-id"></a>
**`getMenuParentId()`**
	
Метод `getMenuParentId` возвращает ИД по умолчанию для родительского элемента меню.

<a name="basic-menu-item-decorator"></a>
### Базовый декоратор элемента меню

Базовый декоратор элемента меню `Laravelayers\Navigation\Decorators\MenuItemDecorator` реализует интерфейс `Laravelayers\Contracts\Navigation\MenuItem` и использует [трейт элемента меню и узла дерева ](#menu-item-trait).

Когда декоратор данных наследует базовый декоратор меню, достаточно реализовать только методы `getMenuName` и `getMenuUrl`, остальные методы в базовом классе будут использоваться как заглушки:

```php
<?php
	
namespace App\Decorators\Article\Category;
	
use Laravelayers\Navigation\Decorators\MenuItemDecorator;
	
class CategoryDecorator extends MenuItemDecorator
{
	/**
	 * Get the menu item name.
	 *
	 * @return string
	 */
	public function getMenuName()
	{
		return $this->name;
	}
	
	/**
	 * Get the menu item url.
	 *
	 * @return string
	 */
	public function getMenuUrl()
	{
		return return route('category.show', ['id' => $this->getKey()]);
	}
}
```

Если объект декоратора меню создать из коллекции, где каждый элемент является массивом, то необходимо вызвать метод `getMenu` декоратора меню, который декорирует каждый элемент с помощью базового декоратора элемента меню:

```php
$menu = MenuDecorator::make(collect([
	0 => [
		'id' => 1,
		'name' => 'Element 1',
		'url' => '/element/1',
		'parent_id' => 0
	],
	0 => [
		'id' => 2,
		'name' => 'Element 2',
		'url' => '/element/2',
		'parent_id' => 1
	],
]));

$menu->getMenu();
	
/*
	MenuDecorator {
	  #original: null
	  #treeMethod: []
	  #dataKey: "items"
	  #items: Collection {
	    #items: array: [
	      0 => MenuItemDecorator {}
	      1 => MenuItemDecorator {}
	    ]
	  }
	  #visibleProperties: []
	  #visibleGetters: []
	}
*/

$menu->getMenu()->count();

// 2
```
	
В таком случае ключи элементов массива должны соответствовать ключам, используемым методами базового декоратора для получения данных из массива:

Ключ      | Метод
----------|------------------
id        | `getKeyName`
name      | `getMenuName`
url       | `getMenuUrl`
icon      | `getMenuIcon`
sorting   | `getMenuSorting`
class     | `getMenuClass`
parent_id | `getMenuParentId`

После этого можно вызывать методы [построения дерева](#building-tree):

```php
$menu->getMenu()->getTree()->count();

// 1

$menu->getMenu()->getTree()->first()->getTree()->isNotEmpty();

// true
```

<a name="menu-item-trait"></a>
### Трейт элемента меню

Трей элемента меню и узла дерева `Laravelayers\Navigation\Decorators\MenuItem` используется при [определении декоратора элемента меню и узла дерева](#defining-menu-item-decorator), также реализует [интерфейс элемента формы](forms.md#form-element-interface)

После построения дерева каждый элемент меню является узлом дерева, содержит поддерево и уровень вложенности:

```php
// App\Services\Article\CategoryService

$this->repository
	->get()
	->getTree()
	->first()

/*
	CategoryDecorator {
		#original: MenuDecorator {}
		#dataKey: "data"
		#data: array:8 [
		  "id" => 1
		  ...
		]
		#primaryKey: "id"
		#menu: array: 2 [
			"level" => 0
			"tree" => Collection {}
		]		
		
		...
	}
*/
```
	
В трейте определены следующие методы:

- [`getMenuLabel`](#get-menu-label)
- [`getNodeId`](#get-node-id)
- [`getNodeParentId`](#get-node-parent-id)
- [`getNodeSorting`](#get-node-sorting)
- [`getNodeLevel`](#get-node-level)
- [`getIsNodeSelected`](#get-is-node-selected)
- [`getTree`](#get-item-tree)
- [`getSiblings`](#get-item-siblings)
- [`getPath`](#get-item-path)
- [`getParent`](#get-item-parent)

<a name="get-menu-label"></a>
**`getMenuLabel()`**

Метод `getMenuLabel` возвращается массив со значением и классом для метки отображаемой после названия меню внутри HTML элемента [`<span class="label alert">`](https://get.foundation/sites/docs/label.html). Для добавление метки необходимо использовать метод `setMenuLabel`, принимающий в качестве первого аргумента значение метки, в качестве второго - значение HTML атрибута класса.

По умолчанию метод возвращает пустую строку для пути меню, используя для проверки метод `getTreeMethod('path')`.

<a name="get-node-id"></a>
**`getNodeId()`**

Метод `getNodeId` возвращает ИД узла дерева с помощью метода [`getKey`](decorators.md#get-primary-key).

> Обратите внимание, что в случае если элемент использовался для [добавления в узел дерева](#add-nodes), тогда метод возвращает измененный ИД узла дерева, в котором значение, возвращаемое методом [`getNodeParentId`](#get-node-parent-id), используется в качестве префикса.

<a name="get-node-parent-id"></a>
**`getNodeParentId()`**

Метод `getNodeParentId` возвращает ИД родительского узла дерева с помощью метода [`getMenuParentId`](#get-menu-parent-id).

> Обратите внимание, что значение может быть изменено с помощью метода `setNodeParentId`, если для элемента [добавлялись узлы дерева](#add-nodes).

<a name="get-node-sorting"></a>
**`getNodeSorting()`**

Метод `getNodeSorting` возвращает порядок сортировки узла дерева с помощью метода [`getMenuSorting`](#get-menu-sorting).

> Обратите внимание, что значение может быть изменено с помощью метода `setNodeSorting`, если элемент использовался для [добавления в узел дерева](#add-nodes).

<a name="get-node-level"></a>
**`getNodeLevel()`**

Метод `getNodeLevel` возвращает уровень вложенности узла дерева. Изменяется с помощью метода `setNodeLevel` при [построении дерева](#building-tree).

<a name="get-is-node-selected"></a>
**`getIsNodeSelected()`**

Метод `getIsNodeSelected` возвращает `true`, если узел дерева выбран, или `false`. Для изменения значения используется метод `setIsNodeSelected`.

> Обратите внимание, что метод [`setIsSelected`](decorators.md#get-is-selected) также вызывает метод `setIsNodeSelected`.

<a name="get-item-tree"></a>
**`getTree()`**

Метод `getTree` возвращает поддерево узла дерева:

```php
$this->repository->get()
	->getTree()
	->first()
	->getTree();

/*
	MenuDecorator {
		#original: MenuDecorator {}
		#treeMethod: []
		#dataKey: "items"
		#items: Collection {}
		...
	}	
*/
```
	
<a name="get-item-siblings"></a>
**`getSiblings()`**

Метод `getSiblings` возвращает подуровень узла дерева:

```php
$this->repository->get()
	->getTree()
	->first()
	->getSiblings();
```
	
<a name="get-item-path"></a>
**`getPath()`**

Метод `getPath` возвращает путь для узла дерева. Метод может принимать в качестве второго аргумента максимальное количество родителей до которых будет загружен путь, например, `1` для загрузки только родительского узла дерева:

```php
$this->repository->get()
	->getNode(5)
	->first()
	->getPath(1);
```
	
<a name="get-item-parent"></a>
**`getParent()`**

Метод `getParent` возвращает родительский узел и его поддерево для узла дерева:

```php
$this->repository->get()
	->getNode(5)
	->first()
	->getParent();
```

<a name="building-tree"></a>
## Построение дерева

Для построения дерева в декораторе меню `Laravelayers\Navigation\Decorators\MenuDecorator`, создается экземпляр класса `Laravelayers\Navigation\Tree`, который принимает коллекцию элементов и возвращает дерево. Класс реализует интерфейс `Laravelayers\Contracts\Navigation\Tree`, который связан с ним в сервис-провайдере `Laravelayers\Navigation\NavigationServiceProvider`:

```php
// Laravelayers\Navigation\NavigationServiceProvider::register()
	
$this->app->bind(
    \Laravelayers\Contracts\Navigation\Tree::class,
    \Laravelayers\Navigation\Tree::class
);
```

В декораторе меню определены следующие методы для получения узлов дерева:

- [`getTree`](#get-tree)
- [`getSiblings`](#get-siblings)
- [`getPath`](#get-path)
- [`getParent`](#get-parent)
- [`getNode`](#get-node)
- [`getTitle`](#get-title)
- [`getOriginal`](#get-original)
- [`getSelectedItems`](#get-selected-items)
- [`addNodes`](#add-nodes)
- [`reloadNodes`](#reload-nodes)
- [`getTreeMethod`](#get-tree-method)

<a name="get-tree"></a>
**`getTree()`**

Метод `getTree` возвращает дерево, где каждый элемент является [узлом дерева](#defining-menu-item-decorator):

```php	
// App\Services\Article\CategoryService
	
$this->repository
	->get()
	->getTree();
	
/*
	MenuDecorator {
		#original: MenuDecorator {}
		#treeMethod: []
		#dataKey: "items"
		#items: Collection {
			#items: array:5 [
				0 => CategoryDecorator {
					#original: MenuDecorator {}
					#dataKey: "data"
					#data: array:8 [
					  "id" => 1
					  "name" => "Name"
					  "image" => "1.png"
					  "icon" => "icon-plus"
					  "status" => 1
					  "categoryTree" => DataDecorator {}
					]
					#primaryKey: "id"
					#menu: array: 2 [
						"level" => 0
						"tree" => Collection {}
					]
					...
				}
				1 => CategoryDecorator {}
				2 => CategoryDecorator {}
				3 => CategoryDecorator {}
				4 => CategoryDecorator {}
			]
		}
		...
	}	
*/
```
	
Метод может принимать в качестве первого аргумента объект или ИД узла дерева для которого будет загружено поддерево, в качестве второго аргумента может принимать максимальный уровень до которого будет загружено дерево, например, `0` для загрузки только подуровня для указанного ИД узла дерева:

```php
$this->repository
	->get()
	->getTree(2, 0);
```

<a name="get-siblings"></a>
**`getSiblings()`**

Метод `getSiblings` возвращает подуровень для указанного объекта или ИД элемента меню, где каждый элемент меню является [узлом дерева](#defining-menu-item-decorator):

```php
$this->repository
	->get()
	->getSiblings(2);
```
	
<a name="get-path"></a>
**`getPath()`**

Метод `getPath` возвращает путь для указанного объекта или ИД элемента меню, где каждый элемент является [узлом дерева](#defining-menu-item-decorator). Метод может принимать в качестве второго аргумента максимальное количество родителей до которых будет загружен путь, например, `1` для загрузки только родительского узла дерева:

```php
$this->repository
	->get()
	->getPath(2, 1);
```
	
<a name="get-parent"></a>
**`getParent()`**

Метод `getParent` возвращает родительский узел и его поддерево для указанного объекта или ИД элемента меню, где каждый элемент является [узлом дерева](#defining-menu-item-decorator):

```php
$this->repository
	->get()
	->getParent(2);
```
	
<a name="get-node"></a>
**`getNode()`**

Метод `getNode` возвращает узел дерева и его поддерево для указанного объекта или ИД элемента меню, где каждый элемент является [узлом дерева](#defining-menu-item-decorator). Метод может принимать в качестве второго аргумента максимальный уровень до которого будет загружено дерево, например, `1` для загрузки только подуровня для указанного ИД узла дерева:

```php
$this->repository
	->get()
	->getNode(2, 1);
```

<a name="get-title"></a>
**`getTitle()`**

Метод `getTitle` возвращает строку из названий элементов пути расположенных в обратном порядке:

```php
$menu->getPath(2)->implode('name', ' / ');
	
// Users / Actions
	
$menu->getTitle(2);
	
// Actions / Users
```

В качестве первых двух аргументов метод принимает те же аргументы, что и метод [`getPath`](#get-path). В качестве третьего аргумента метод может принимать текст, который будет добавлен в конце строки. В качестве четвертого аргумента метод может принимать разделитель для названий элементов пути. Если же вызвать метод `getTitle` сразу после вызова метода `getPath`, то текст и разделитель можно передать в качестве первых двух аргументов:

```php
$path = $menu->getPath(2);
	
$path->getTitle('Administration', ' | ');
	
// Actions | Users | Administration
```
	
<a name="get-original"></a>
**`getOriginal()`**	

Метод `getOriginal` возвращает оригинальную коллекцию элементов:

```php
$this->repository
	->get()
	->getTree()
	->getOriginal();	
	
/*
	TreeDecorator {
		#original: null
	  	#treeMethod: array:2 [
    		0 => "getTree"
    		1 => []
    	]
		#dataKey: "items"
		#items: Collection {
			#items: array:5 [
				0 => CategoryDecorator {}
				1 => CategoryDecorator {}
				2 => CategoryDecorator {}
				3 => CategoryDecorator {}
				4 => CategoryDecorator {}
			]
		}
		...
	}	
*/
```

Метод `hasOriginal` определяет, существует ли оригинальная коллекция элементов:

```php
$this->repository
	->get()
	->getTree()
	->hasOriginal();
	
// true
	
$this->repository
	->get()
	->hasOriginal();
	
// false
```

<a name="add-nodes"></a>
**`addNodes()`**

Метод `addNodes` принимает коллекцию или массив элементов для добавления в указанный узел дерева. В качестве второго и третьего аргументов, метод может принимать те же аргументы, что и метод [`addNode`](#add-node), в который передается каждый элемент коллекции в качестве первого аргумента:

```php
$tree = $this->repository
	->get()
	->getTree(2);
	
$tree->count(); 
	
// 6		

$nodes = $tree->getOriginal()->whereIn('id', [6, 7]);
	
$tree = $tree->addNodes($nodes, 2, 0);
	
$tree->count();
	
// 8
```

<a name="add-node"></a>	
Метод `addNode` принимает объект декоратора элемента меню и возвращает [оригинальную коллекцию элементов](#get-original). Метод может принимать в качестве второго аргумента ИД родительского узла дерева, в качестве третьего аргумента может принимать ключ узла дерева перед которым должен быть добавлен узел:

```php
$first = $nodes->first();
	
$first->getNodeId();
	
// 1
	
$path = $tree->getPath(2);
	
$path->count();
	
// 1

$path = $path->addNode($first, 2)->getPath(1);
	
$path->count();
	
// 2
	
$path->last()->getNodeId();
	
// 1_2
```
	
> Обратите внимание, что при вызове метода `addNode`, для узлов дерева могут быть изменены следующие значения: [ИД узла дерева](#get-node-id), [ИД родительского узла дерева](#get-node-parent-id), [порядок сортировки](#get-node-sorting), [уровень вложенности](#get-node-level).   

<a name="reload-nodes"></a>
**`reloadNodes()`**

Метод `reloadNodes` предназначен для перезагрузки дерева из оригинальной коллекции, например, после добавления нового узла дерева:
	
```php
$tree = $this->repository
	->get()
	->getTree(2);
	
$tree = $tree->addNode($nodes->first(), 2, 0)->reloadNodes();
// $tree = $tree->addNode($nodes->first(), 2, 0)->getTree(2);
	
$tree->hasOriginal();
	
// true
```

<a name="get-tree-method"></a>
**`getTreeMethod()`**
	
Метод `getTreeMethod` возвращает массив с названием текущего метода, используемого для получения элементов дерева, и параметрами метода. Может принимать имя метода, для проверки соответствия текущему методу.

<a name="menu-display"></a>
## Отображение меню
	
Метод `render` декоратора меню используется для отображения элементов с помощью шаблонов представлений, совместимых c фронтенд фреймворком [Foundation](https://get.foundation/sites/docs/):

```php
$menu = $this->repository->get();
	
$menu->render();
//$menu->render('menu');
//(string) $menu;
	
// navigation::layouts.menu
	
$menu->getPath(2)->render();
	
// navigation::layouts.breadcrumbs.nav.menu
```
	
Метод может принимать путь до одного из доступных представлений внутри `navigation::layouts` или `layouts.menu` с именем `menu`.

> Обратите внимание, что при преобразовании объекта декоратора меню в строку также будет вызываться метод `render`.

[Accordion menu](https://get.foundation/sites/docs/accordion-menu.html): 

```php
$tree = $menu->getTree();
	
$tree->render('accordion.drilldown');
	
// navigation::layouts.accordion.drilldown.menu

$tree->render('accordion');
	
// navigation::layouts.accordion.menu
```
	
[Breadcrumbs](https://get.foundation/sites/docs/breadcrumbs.html):

```php	
$menu->getPath(2)->render('breadcrumbs');
	
// navigation::layouts.breadcrumbs.menu	
```	

[Drilldown menu](https://get.foundation/sites/docs/drilldown-menu.html):

```php
$tree->render('drilldown');

// navigation::layouts.drilldown.menu	
```

[Dropdown menu](https://get.foundation/sites/docs/dropdown-menu.html):	

```php
$tree->render('dropdown.accordion');

// navigation::layouts.dropdown.accordion.menu
	
$tree->render('dropdown.drilldown');

// navigation::layouts.dropdown.drilldown.menu
	
$tree->render('dropdown.vertical');
	
// navigation::layouts.dropdown.vertical.menu
	
$tree->render('dropdown');
	
// navigation::layouts.dropdown.menu	
```

[Menu](https://get.foundation/sites/docs/menu.html):

```php
$tree->render('vertical');

// navigation::layouts.vertical.menu	
```

Также вы можете использовать представления напрямую:

```php
@include('navigation::layouts.menu', ['tree' => $tree])

@include('navigation::layouts.breadcrumbs.nav.menu', ['tree' => $menu->getPath(2)])
```
	
Для публикации представлений выполните команду:

```php
php artisan vendor:publish --tag=laravelayers-menu
```

Для добавления нового представления меню, необходимо создать поддиректорию и файл `menu.blade.php` внутри `resources/views/layouts/menu/` или `resources/views/vendor/menu/layouts/`:

```php	
$tree->render('test');

// layouts.menu.test
```
