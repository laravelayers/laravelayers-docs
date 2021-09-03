# Previous URL 

- [Introduction](#introduction)
- [Use](#using)

<a name="introduction"></a>
## Introduction

The class `Laravelayers\Previous\PreviousUrl` is used to get the previous URL on the page with the form and to return to it after submitting the form.

<a name="using"></a>
## Usage

To go to the form page and, after submitting it, return to the current URL, you need to generate a link with a hash of the current URL to go to the form URL, for example, using the static `addHash` method of the `Laravelayers\Previous\PreviousUrl` class:

```php
// https://localhost/admin/users
	
PreviousUrl::hash();
	
// 804d68f0a2a7fc3aa889a69e4d5e0ac4	

PreviousUrl::addHash(route('admin.user.create'));
	
// https://localhost/admin/users/create?previous=804d68f0a2a7fc3aa889a69e4d5e0ac4
```
	
To submit a form with a hash of the previous URL, you need to generate a link for the `action` attribute of the HTML form tag `form`, for example, using the static method `addQueyrHash`:

```php
// https://localhost/admin/users/create?previous=804d68f0a2a7fc3aa889a69e4d5e0ac4

PreviousUrl::getInput();
	
// 804d68f0a2a7fc3aa889a69e4d5e0ac4

PreviousUrl::addQueryHash(route('admin.user.store'));
	
// https://localhost/admin/users?previous=804d68f0a2a7fc3aa889a69e4d5e0ac4
```

To return to the previous URL after submitting the form, you need to get the link of the previous URL, for example, using the `getUrlFromQuery()` method:

```php
$previousUrl = PreviousUrl::getUrlFromQuery();
	
// https://localhost/admin/user
	
redirect($previousUrl);
```
	
Or if you connect the interceptor `previous.url` and, after submitting the form, save in the session using the `flash` method the value `true` for the key with the name `PreviousUrl::getRedirectInputName()`, then after returning to the URL of the form, a redirect to the previous URL, as if calling the [`validate`](forms.md#validation) method of the form decorator.

To change the parameter name of the previous URL, use the `setInputName` method:

```php
PreviousUrl::getInputName(); 
	
// previous
	
PreviousUrl::setInputName('prev');
	
PreviousUrl::getInputName(); 
	
// prev
```
	
> Note that if you set the previous URL parameter to an empty value, then there will be no return to the previous URL after submitting the form.
