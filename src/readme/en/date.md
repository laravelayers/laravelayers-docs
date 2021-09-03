# Date and Time

- [Introduction](#introduction)
- [Configuration](#configuration)
- [Localization](#localization)
- [Carbon macros](#carbon-macros)
- [Blade directives](#blade-directives)

<a name="introduction"></a>
## Introduction

Additional configuration values, Carbon macros and Blade directives are used to display the date and time in a consistent format.

<a name="configuration"></a>
## Configuration

Date and time configuration values are used in Carbon macros, Blade directives, and corresponding [form decorator](forms.md#types) views:

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

> The default for `datetime.format` is `datetime.format time.format`.

The `registerConfigFiles` method of the `Laravelayers\Date\DateServiceProvider` class is set to the default format used when converting the `Illuminate\Support\Carbon` instance to a string:

```php
Carbon::setToStringFormat(config('date.datetime.format'));
```

To publish the configuration files of the date and time format, run the command:

```php
php artisan vendor:publish --tag=laravelayers-date
```

<a name="localization"></a>
## Localization

The `registerLocale` method of the`Laravelayers\Date\DateServiceProvider` class is localized for the `Illuminate\Support\Carbon` class using the `date.locale` configuration value. If `date.locale` is `null` (by default), then the value `app.locale` is used, if an empty string is specified, then the localization will not be set.

<a name="carbon-macros"></a>
## Carbon macros

The `Laravelayers\Date\CarbonMacros` class has macros set for the `Illuminate\Support\Carbon` class:

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
## Blade Directives

The `Laravelayers\Date\BladeDirectives` class has [Blade directives](https://laravel.com/docs/blade#extending-blade) set, which use [Carbon macros](#carbon-macros):

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
