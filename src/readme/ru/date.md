# Дата и время

- [Введение](#introduction)
- [Конфигурация](#configuration)
- [Локализация](#localization)
- [Макросы Carbon](#carbon-macros)
- [Директивы Blade](#blade-directives)

<a name="introduction"></a>
## Введение

Для отображения даты и времени в едином формате, используются дополнительные значения конфигурации, макросы Carbon и директивы Blade.

<a name="configuration"></a>
## Конфигурация

Значения конфигурации даты и времени используются в макросах Carbon, директивах Blade, и соответствующих представлениях [декоратора формы](forms.md#types):

```php	
/*
	array:5 [
		"locale" => "en"
		"formatLocalized" => "%e %B %Y"
		"format" => "d.m.Y"
		"time" => array:1 [
			"format" => "H:i:s"
		]
		"datetime" => array:1 [
			"format" => "d.m.Y H:i:s"
		]
	]	
*/
```

> Значение по умолчанию для `datetime.format` равно `datetime.format time.format`.

В методе `registerConfigFiles` класса `Laravelayers\Date\DateServiceProvider` установлен формат по умолчанию, используемый при преобразовании экземпляра `Illuminate\Support\Carbon` в строку:

```php
Carbon::setToStringFormat(config('date.datetime.format'));
```

Для публикации файлов конфигурации формата даты и времени, выполните команду:

```php
php artisan vendor:publish --tag=laravelayers-date
```

<a name="localization"></a>
## Локализация

В методе `registerLocale` класса `Laravelayers\Date\DateServiceProvider` установлена локализация для класса `Illuminate\Support\Carbon` с помощью значения конфигурации `date.locale`. Если `date.locale` равно `null` (по молчанию), то используется значение `app.locale`, если указана пустая строка, то локализация не будет установлена.

<a name="carbon-macros"></a>
## Макросы Carbon

В классе `Laravelayers\Date\CarbonMacros` установлены макросы для класса `Illuminate\Support\Carbon`:

```php	
$date = Carbon::create(2012, 1, 1, 13, 10, 30);
	
(string) $date;
	
// 01.01.2012 13:10:30
	
$date->toConvertedDateString(); 
	
// 01.01.2012
    
$date->toConvertedTimeString()); 
	
// 13:10:30
	
$date->toConvertedDateTimeString();
	
// 01.01.2012 13:10:30
	
$date->dateLocalized();
	
// 1 January 2012
	
$date->createFromDefaultFormat($date);
	
/*
	Carbon @1325409030 {
		date: 2012-01-01 13:10:30.0 Europe/Moscow (+04:00)
	}	
*/
```

<a name="blade-directives"></a>
## Директивы Blade

В классе `Laravelayers\Date\BladeDirectives` установлены [директивы Blade](https://laravel.com/docs/blade#extending-blade), которые используют [макросы Carbon](#carbon-macros):

```php
@dateNow()
	
// 01.01.2018 13:10:30
	
@date('2012-01-01 13:10:30')
	
// 01.01.2012
	
@time('2012-01-01 13:10:30')
	
// 13:10:30
	
@datetime('2012-01-01 13:10:30')
	
// 01.01.2012 13:10:30
	
@dateLocalized('2012-01-01 13:10:30')
	
// 1 January 2012
	
@dateDiffForHumans('2012-01-01 13:10:30')
	
// 6 years ago
```
