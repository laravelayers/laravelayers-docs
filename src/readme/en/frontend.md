# Frontend

- [Introduction](#introduction)
- [Installing Foundation and other NPM packages](#installation)
- [Views](#view)
	- [Page layout](#page-layout)
	- [Additional layouts](#additional-layouts)
- [SASS](#sass)
	- [Icons](#icons)
- [Javascript](#javascript)
	- [Adding Javascript Plugins](#adding-javascript-plugins)

<a name="introduction"></a>
## Introduction
	
[Foundation ZURB](https://get.foundation/sites) is used as a frontend framework.

![Laravelayers page layout](../../storage/images/frontend-page-layout.png)

<a name="installation"></a>
## Installing Foundation and other NPM packages

During [installing Laravelayers](installation.md), you must first run the following Artisan command:

```php
php artisan laravelayers:install --no-interaction
```
	
Then install dependencies from `package.json` and compile CSS and JS files:
	
```php
npm install && npm run dev
```
	
This will install the following NPM packages along with Foundation:

- [Cropper.js](https://fengyuanchen.github.io/cropperjs/)
- [Flatpickr](https://flatpickr.js.org)
- [Font Awesome](https://fontawesome.com)
- [jQuery UI](https://jqueryui.com)
- [LibPhoneNumber](https://github.com/catamphetamine/libphonenumber-js)
- [Quill](https://quilljs.com)
- [SimpleMDE](https://simplemde.com)
- [Validator.js](https://github.com/validatorjs/validator.js)

The following files and directories will be copied:

```php
|-resources/
|	|-js/
|	|	|-vendor/
|	|	|	|-admin/
|	|	|	|-foundation/
|	|	|
|	|	|-app.js
|	|	|-app.admin.js
|	|	|-bootstrap.js
|	|
|	|-sass/
|	|	|-default/
|	|	|	|-layouts/
|	|	|	|-settings/
|	|	|		|-_settings.scss
|	|	|
|	|	|-vendor/
|	|	|	|-admin/
|	|	|	|-foundation/
|	|	|
|	|	|-app.scss
|	|	|-app.admin.scss
|	|
|	|-lang/
|	|	|-vendor/
|	|		|-admin/
|	|		|-foundation/
|	|
|	|
|	|-views/
|		|-vendor/
|			|-admin/
|			|-foundation/
|
|-webpack.mix.js
```	

> Please note that when copying, all existing files with the same name will be renamed to files with the extension `.bak`.

To copy all Javascript and Sass files again, run the Artisan command without the `--no-interaction` option:

```php
php artisan laravelayers:install
```
	
> Обратите внимание, что файл с настройками Sass для фронтенд фреймворка Foundation будет скопирован в `resources/sass/default/settings/_settings.scss`, только если файла не существует.
	
To copy the files back to the `resources` directory only, run the following Artisan commands:

```php
php artisan vendor:publish --tag laravelayers-foundation
php artisan vendor:publish --tag laravelayers-admin
```

<a name="view"></a>
## Views

The `/resources/view/vendor/foundation/layouts` directory contains default views that are linked using the `foundation` namespace.

<a name="page-layout"></a>
### Page Layout

The `foundation::layouts.app` page layout includes the following views by default:

```php
|- admin::layouts.menuBar
|
|- header
|	|- topBar
|	|	|- admin::layouts.topBar
|	|	|
|	|	|- titleBar
|	|	|	|- titleBarLeft
|	|	|	|- titleBarRight 
|	|	|
|	|	|- topBarLeft
|	|	|- topBarCenter
|	|	|
|	|	|- topBarRight
|	|		|- auth::layouts.auth
|	|
|	|- breadcrumbs
|	|- headerBar
|	|
|	|- simpleTopBar
|		|- simpleTitleBar
|		|	|- titleBarLeft
|		|	|- titleBarRight
|		|
|		|- topBarLeft
|		|- auth::layouts.auth
|
|- main
|	|- sidebarLeft
|	|- content
|	|- sidebarRight
|	
|- footer
```

An example of using a page layout:

```php
@extends('foundation::layouts.app', ['class' => 'app', 'title' => 'App', 'simple' => false, 'clear' => false, 'full' => false])

@push('head')

	<style>
		body {background-color: white;}
	</style>

@endpush

@section('breadcrumbs')

	{{ $path->render('breadcrumbs.menu') }}

@endsection

@section('breadcrumbsRight')

	<div data-responsive-toggle="sidebar_left" data-hide-for="large">
		<a data-toggle="sidebar_left">
			@icon('icon-bars')
		</a>
	</div>

@endsection

@section('headerBar')

	{{--heade bar--}}

@endsection

@section('sidebarLeft')

	@component('foundation::layouts.sidebarLeft')

		<div class="sticky" data-sticky data-anchor="main" data-sticky-on="large" data-margin-top="4">
			Sidebar left
		</div>

	@endcomponent

@endsection

@section('content')

	@component('foundation::layouts.content')

        {!! $content !!}

	@endcomponent
	
@endsection

@section('sidebarRight')

    @if (!empty($content_right))

        @component('foundation::layouts.sidebarRight')

            {!! $content_right !!}

        @endcomponent

    @endif

@endsection

@push('scripts')

	<script>
		console.log('app');
	</script>

@endpush

@section('footer')

	@component('foundation::layouts.footer')

		<div class="cell text-right">
			<small>© {{ config('app.name', 'Laravel') }}</small>
		</div>

	@endcomponent

@endsection
```

> Note that the `foundation::layouts.app` view includes sections, so you can replace the content of those sections on the page.

**`$class`**

The value of the `class` variable passed to the page layout will be added as a class for the page body.

**`$title`**

The value of the `title` variable passed to the page layout will be added to the HTML `<title>` element.

**`$simple`**

If a non-empty `$simple` variable is passed to the page layout, then the variable will be passed to the`foundation::layouts.header` view.

**`$clear`**

If a non-empty `clear` variable is passed, then the `foundation::layouts.header` and `foundation::layouts.footer` views will not be included in the page layout.

**`$full`**

If a non-empty `$full` variable is passed, then the `foundation::layouts.header` and `foundation::layouts.footer` views will not be included in the page layout, and also the variable will be passed to the `foundation::layouts.main` view to add `100%` height  to content and align content to height and width, for example, center `<div class="grid-x grid-padding-x grid-padding-y align-middle">`.  

**`head`**

With the `head` section, you can pass additional content inside the HTML `<head> `element.

**`foundation::layouts.header`**

The foundation::layouts.header` view includes the `admin::layouts.topBar`, `foundation::layouts.topBar`, `foundation::layouts.breadcrumbs`, `foundation::layouts.headerBar` views.

> Note that the `foundation::layouts.header` view includes sections, so you can replace the content of those sections on the page.
 
If a non-empty `$simple` variable is passed, then only the `admin::layouts.topBar` and `foundation::layouts.simpleTopBar` views will be included.

> Note that the views included in the `foundation::layouts.header` include the views in the `/resources/view/layouts` directory that match the names of the `foundation::layouts` views if they exist.

**`admin::layouts.topBar`**

The `admin::layouts.topBar` view is the top panel for users [authorized](auth.md#authorization) with administrator privileges, which expands the administration menu sidebar, which is displayed using the `admin::layouts.menuBar` view, included in the page layout.

**`foundation::layouts.topBar`**

The `foundation::layouts.topBar` view is the top bar that includes the `foundation::layouts.titleBar` view for display on [small screens](https://get.foundation/sites/docs/media-queries.html).

**`foundation::layouts.simpleTopBar`**

The `foundation::layouts.simpleTopBar` view is a simple top bar that includes the `foundation::layouts.simpleTitleBar` view for display on [small screens](https://get.foundation/sites/docs/media-queries.html). 

A simple top bar is included in [authentication](auth.md#publishing-views-and-translations) views.

**`foundation::layouts.breadcrumbs`**

The `foundation::layouts.breadcrumbs` view is used to display the navigation chain and additional content on the right that can be passed using the `breadcrumbsRight` section. Also included in `admin::layouts.breadcrumbs`.

> Note that in the example above, using the page layout, using the `breadcrumbsRight` section, adds a menu icon displayed on medium and small screens that displays the contents of the left sidebar.

**`foundation::layouts.headerBar`**

The `foundation::layouts.headerBar` view is used to display additional content below the navigation chain. Also included in `admin::layouts.headerBar`.

**`foundation::layouts.main`**

The `foundation::layouts.main` view is the layout of the main content of the page, which also includes the sidebar sections.

**`foundation::layouts.sidebarLeft`**

The `foundation::layouts.sidebarLeft` view is the content of the left sidebar.

> Note that in the example above for using the page layout, the left sidebar uses the [Foundation Sticky](https://get.foundation/sites/docs/sticky.html) Javascript plugin.

**`foundation::layouts.content`**

The `foundation::layouts.main` view is the main content of the page.

**`foundation::layouts.sidebarRight`**

The `foundation::layouts.sidebarRight` view is the content of the right sidebar.

**`scripts`**

With the `scripts` section you can pass additional content to the end of the `<body>` HTML element.

**`foundation::layouts.footer`**

The `footer` view is used to display at the end of the page and can be passed using the `footer` section or added to the `/resources/view/layouts/footer.blade.php` view.

<a name="additional-layouts"></a>
### Additional layouts

The following views are basic and are used by other views, including the [`form::layouts`](forms.md#types) views:

- `foundation::layouts.icon - used to display [icons](#icons).
- `foundation::layouts.callout` - used to display [warnings](https://get.foundation/sites/docs/callout.html).
- `foundation::layouts.preloader` - used to include a hidden block with the image of the `icon-spinner` loading icon.

The following views are basic and are used in PHP classes, including in the [`Laravelayers\Admin\Decorators\DataDecorator`](admin.md#data-decorators) trait in the administration panel in the methods used to [transform the display text](#render-methods):

- `foundation::layouts.a` - designed to display links, with the ability to add classes for each link. Also included in `admin::layouts.table.a`.
- `foundation::layouts.div` - includes the `foundation::layouts.span` view within the `<div>` HTML element. Also included in `admin::layouts.table.div`.
- `foundation::layouts.span` - designed to display text inside HTML elements `<span>`, with the ability to add classes for each element.
- `foundation::layouts.tooltip` - designed to display text with a tooltip. Also included in `admin::layouts.table.tooltip`.
- `foundation::layouts.ul` - designed to display text within HTML `<li> `elements for a list or numbered list. Also included in `admin::layouts.table.ul`.

<a name="sass"></a>
## SASS

The structure of the `/resources/sass/vendor/foundation` directory is by default similar to the structure of the `scss` directory of the NPM package `foundation-sites`.

The `layouts` subdirectory contains the SCSS files corresponding to the views in the `/resources/sass/vendor/foundation/layouts` directory.

The `/resources/sass/default` directory is for custom files by default, instead of making changes to `/resources/sass/vendor/foundation`. The default directory contains the file `settings/_settings.scss` with the Sass settings for the Foundation frontend framework.

All SCSS files are included in `/resources/sass/vendor/foundation/app.scss`, which is added by default to `webpack.mix.js`. 

The same structure is in the directory `/resources/sass/vendor/admin`. SCSS files are included in the file `/resources/sass/vendor/foundation/app.admin.scss`, which is added by default to `webpack.mix.js`.

<a name="icons"></a>
### Icons

The `/resources/sass/_fontawesome.scss` file includes icons from the `@fortawesome` NPM package.

> Note that the file `/resources/sass/layouts/_icon.scss` defines the prefix `icon` instead of `fa` for the [Font Awesome](https://fontawesome.com) icon classes. 

Also added the ability to change icon colors by adding classes according to the color map (`primary`, `secondary`, `success`, `warning`, and `alert`), which can be changed in the variable `$foundation-palette` in the file `/resources/sass/default/settings/_settings.scss`.

The `Laravelayers\Foundation\BladeDirectives` class has a [Blade directive](https://laravel.com/docs/blade#extending-blade) `@icon` that uses the `foundation::layouts.icon` view to display icons:

```php
@icon('icon-plus icon-fw alert', 'id="test"')
	
/**
	<i class="icon icon-plus icon-fw alert" id="test"></i>
**/
```

<a name="javascript"></a>
## Javascript

The structure of the `/resources/js/vendor/foundation` directory is by default similar to the structure of the `js` directory of the NPM package `foundation-sites`.

All Javascript files are included in `/resources/js/vendor/foundation/app.js`, which is added by default in `webpack.mix.js`.

The same structure is in the directory `/resources/js/vendor/admin`. Javascript files are included in the file `/resources/js/vendor/foundation/app.admin.js`, which is added by default in `webpack.mix.js`.

<a name="adding-javascript-plugins"></a>
### Adding Javascript plugins

The easiest way to create a custom Foundation Javascript plugin in your application is to run the Artisan command:

```php
php artisan make:js charts --app
```

As a result of executing this command, the plugin `resources/js/plugins/charts.js` will be created:

```javascript
'use strict';
	
import $ from 'jquery';
	
/**
* Charts plugin.
* @module foundation.charts
*/
class Charts {
/**
 * Creates a new instance of Charts.
 * @class
 * @name Charts
 * @fires Charts#init
 * @param {Object} element - jQuery object to add the trigger to.
 * @param {Object} options - Overrides to the default plugin settings.
 */
constructor(element, options = {}) {
	this.$element = element;
	this.options  = $.extend(true, {}, Charts.defaults, this.$element.data(), options);
	
	this.className = 'Charts'; // ie9 back compat
	
	this._init();
	
	Foundation.registerPlugin(this, this.className);
}
	
/**
 * Initializes the Charts plugin.
 * @private
 */
_init() {
	//
	
	this._events();
}
	
/**
 * Initializes events for Charts.
 * @private
 */
_events() {
	//
}
	
/**
 * Destroys an instance of Charts.
 * @function
 */
_destroy() {
	Foundation.unregisterPlugin(this, this.className);
}
}
	
/**
* Default settings for plugin
*/
Charts.defaults = {
//
};
	
export {Charts};
```

The `--app` option will include the plugin in the `resources/js/plugins/plugins.js` file:

```javascript
//...

import { Charts } from './charts.js';
Foundation.plugin(Charts, 'Charts');
```

The `plugins.js` file will be included in the `resources/js/app.js` file if it is not already included:

```javascript
//...

require('./plugins/plugins.js');
```	

By default, plugins are created in the `resources/js/plugins/` directory, but if you specify the slash `/charts` at the beginning of the plugin name, the plugin will be created in the `resources/js/` directory.

In this case, or if the `--app` option is not used, a line will be added at the end of the plugin:

```javascript
Foundation.plugin(Charts, 'Charts');
```	

Now you need to import the necessary modules into the plugin, for example, [Frappe Charts](https://github.com/frappe/charts):

```javascript
import $ from 'jquery';
import { Chart } from "frappe-charts"
```

The `_init` method is used to initialize a plugin, for example:

```javascript
_init() {
	this._get();

	this._events();
}

/**
 * Get data and draw a chart.
 * @returns {object}
 * @private
 */
_get() {
	let _this = this;

	$.ajax({
		headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
		url: this.options.ajaxUrl,
		data: 'chart=1',
		dataType: "json"
	}).done(function (data) {
		let $options = {
			title: _this.options.title,
			data: data,
			type: "axis-mixed", // or 'bar', 'line', 'scatter', 'pie', 'percentage'
			height: 250
		};

		if (typeof _this.options.chartsOptions === 'object') {
			$.extend($options, _this.options.chartsOptions);
		}

		new Chart('#' + _this.$element.attr('id'), $options);
	});
}
```

The `_events` method is used to initialize plugin events, for example:

```javascript
_events() {
	this.$element.on('click.foundation.charts', (event) => {
		this._destroy();
	});
}
```

When using the `foundation::layouts.app` view, the plugin will be registered automatically:

```html
<script src=" {{ mix('/js/app.js') }} "></script>
<script>
	$(document).foundation();
</script>
```

The `_destroy` method is used to unregister a plugin, for example:

```javascript
_destroy() {	
	this.$element.html('');
	
	Foundation.unregisterPlugin(this, this.className);
}
	
// $('#chart').foundation('_destroy');
```
	
You can also add additional settings for the plugin, which can be changed using the corresponding attributes of the HTML element:

```javascript
/**
 * Default settings for plugin
 */
Charts.defaults = {
    /**
     * URL to load data using Ajax.
     * @option
     * @type {string}
     * @default window.location.href
     */
    ajaxUrl: window.location.href,

    /**
     * Chart title.
     * @option
     * @type {string}
     * @default ''
     */
    title: '',

    /**
     * List of options in JSON format.
     * @option
     * @type {string}
     * @default ''
     */
    chartsOptions: ''
};
```
	
After the JS files are compiled using the `npm run dev` command, you need to bind the plugin to the HTML element and add attributes for customization, for example:

```php
<div id="chart" data-charts data-ajax-url="{{ url()->current() }}" data-title="My Awesome Chart"
	 data-charts-options="{{ htmlspecialchars(json_encode(['colors' => ['red', 'green']])) }}">
</div>
```	

In this example, the data for the chart will be received using an Ajax request to the current URL:

```php
/**
 * Display a listing of the resource.
 *
 * @param Request $request
 * @return \Illuminate\View\View|string
 */
public function index(Request $request)
{
	if ($request->ajax()) {
		return json_encode([
			"labels" => [
				"12am-3am", "3am-6pm", "6am-9am", "9am-12am", "12pm-3pm", "3pm-6pm", "6pm-9pm", "9am-12am"
			],
			"datasets" => [
				[
					"name" => "Some Data",
					"type" => "bar",
					"values" => [25, 40, 30, 35, 8, 52, 17, -4]
				],
				[
					"name" => "Another Set",
					"type" => "line",
					"values" => [25, 50, -10, 15, 18, 32, 27, 14]
				]
			]
		]);
	}

	$items = $this->service->paginate($request);

	return view("admin::layouts.action.index", compact('items'));
}
```
