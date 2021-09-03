# Контроллеры

- [Введение](#introduction)
- [Определение контроллеров](#defining-controllers)
	- [Внедрение сервисного слоя](#inject-service-layer)
- [Базовый контроллер](#base-controller)

<a name="introduction"></a>
## Введение

В [слой](layers.md) контроллер внедряется сервисный слой, который используется для обработки входящего запроса, полученный результат используется для возврата ответа.

**Общая UML диаграмма слоистой структуры**

[![General UML diagram of the layered structure](../../storage/images/general-uml-diagram-of-the-layered-structure.svg)](https://lucid.app/documents/view/16a364a8-19b7-4136-a555-02f58b0c696e)

**UML диаграмма последовательности взаимодействия слоев**

[![UML diagram of the sequence](../../storage/images/uml-diagram-of-the-sequence.svg)
](https://lucid.app/documents/view/6c217ff3-3a7a-4806-bdf8-7a9b117c9de4)

<a name="defining-controllers"></a>
## Определение контроллеров

Ниже приведен класс контроллера с внедренным [сервисным слоем](services.md). Обратите внимание, что контроллер расширяет базовый класс контроллера, включенный в Laravelayers.

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
### Внедрение сервисного слоя
	
Простейший способ создать контроллер с внедренным сервисным слоем — выполнить Artisan-команду `make:controller` с опцией `--service`:

```php
php artisan make:controller Character/Character -s
```

В результате выполнения данной команды будут созданы следующие классы:

- Контроллер `App\Http\Controllers\Character\CharacterController`.
- Сервис `App\Services\Character\CharacterService`.
- Репозиторий `App\Repositories\Character\CharacterRepository`.
- Декоратор `App\Decorators\Character\CharacterDecorator`.
- Модель `App\Models\Character\Character`.

UML диаграмма структуры классов:

[![UML diagram of class structure  in Laravelayers](../../storage/images/uml-diagram-of-class-structure.svg)](https://lucid.app/documents/view/4f287ac4-f718-4216-b508-105fa1b035f4)

Указанная поддиректория `Character`, будет создана в директории для каждого слоя. При создании представлений рекомендуется также создавать соответствующую поддиректорию, например, `resources/views/character/index.blade.php`.

Имена классов, кроме модели будут содержать постфикс соответствующий имени слоя `CharacterController`. По умолчанию, все имена классов, файлов и директорий будут преобразованы в строку СamelCase с большой буквой в начале. Для отмены преобразования следует использовать опцию `--nm`:

```php
php artisan make:controller Character/Character --service --nm
```

Для изменения имени класса сервисного слоя следует использовать опцию `--sn`:

```php
php artisan make:controller Character/Character --service --sn Book/Character
```

Для изменения имени класса базового контроллера следует использовать опцию `--rp`:

```php
php artisan make:controller Character/Character --service --rp App/Http/Controllers/BaseController
```

Или переопределить вызов команды создания контроллера в сервис-провайдере:

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
	
Также можно настроить файлы заглушек, используемых при выполнении команды создания контроллера. Для этого следует выполнить команду публикации наиболее распространенных заглушек:

```php
php artisan stub:publish
```
	
<a name="base-controller"></a>
## Базовый контроллер

Базовый класс контроллера `Laravelayers\Foundation\Controllers\Controller` расширяет базовый класс контроллера, включенный в Laravel, определят частное свойство `service` для объекта сервиса и дополнительные методы:

- [`authorizeResource`](#authorize-resource)
- [`getPerPage`](#get-per-page)
- [`getSorting`](#get-sorting)
- [`getStatus`](#get-status)

**`authorizeResource()`**

Метод [`authorizeResource`](auth.md#authorizing-resource-controllers) переопределяет метод базового контроллера Laravel.

**`getPerPage()`**

Метод `getPerPage` возвращает результат вызова метода [`getPerPage`](services.md#get-per-page) сервиса.

**`getSorting()`**

Метод `getSorting` возвращает результат вызова метода [`getSorting`](services.md#get-sorting) сервиса.

```php
request()->route()->getController()->getSorting();
```

**`getStatus()`**

Метод `getStatus` возвращает результат вызова метода [`getStatus`](services.md#get-status) сервиса.