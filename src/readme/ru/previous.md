# Предыдущий URL 

- [Введение](#introduction)
- [Использование](#using)

<a name="introduction"></a>
## Введение

Для получения предыдущего URL на странице с формой и для возврата на него после отправки формы используется класс `Laravelayers\Previous\PreviousUrl`.

<a name="using"></a>
## Использование

Чтобы перейти на страницу формы и после ее отправки вернуться к текущему URL, необходимо сгенерировать ссылку с хешем текущего URL для перехода к URL формы, например, с помощью статического метода `addHash` класса `Laravelayers\Previous\PreviousUrl`:

```php
// https://localhost/admin/users
	
PreviousUrl::hash();
	
// 804d68f0a2a7fc3aa889a69e4d5e0ac4	

PreviousUrl::addHash(route('admin.user.create'));
	
// https://localhost/admin/users/create?previous=804d68f0a2a7fc3aa889a69e4d5e0ac4
```
	
Чтобы отправить форму с хешем предыдущего URL, необходимо сгенерировать ссылку для атрибута `action` HTML-тега формы `form`, например, с помощью статического метода `addQueyrHash`:

```php
// https://localhost/admin/users/create?previous=804d68f0a2a7fc3aa889a69e4d5e0ac4

PreviousUrl::getInput();
	
// 804d68f0a2a7fc3aa889a69e4d5e0ac4

PreviousUrl::addQueryHash(route('admin.user.store'));
	
// https://localhost/admin/users?previous=804d68f0a2a7fc3aa889a69e4d5e0ac4
```

Чтобы после отправки формы вернуться на предыдущий URL, необходимо получить ссылку предыдущего URL, например, с помощью метода `getUrlFromQuery()`:

```php
$previousUrl = PreviousUrl::getUrlFromQuery();
	
// https://localhost/admin/user
	
redirect($previousUrl);
```
	
Или если подключить перехватчик `previous.url` и после отправки формы сохранить в сессии с помощью метода `flash` значение `true` для ключа с именем `PreviousUrl::getRedirectInputName()`, тогда после возврата на URL формы, будет выполнен редирект на предыдущий URL, как при вызове метода [`validate`](forms.md#validation) декоратора формы.

Чтобы изменить имя параметра предыдущего URL необходимо использовать метод `setInputName`:

```php
PreviousUrl::getInputName(); 
	
// previous
	
PreviousUrl::setInputName('prev');
	
PreviousUrl::getInputName(); 
	
// prev
```
	
> Обратите внимание, если установить пустое значение параметра предыдущего URL, то не будет выполнен возврат к предыдущему URL после отправки формы.
