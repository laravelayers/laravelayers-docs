<?php

namespace Laravelayers\Docs;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravelayers\Docs\Middleware\Locale;
use Laravelayers\Navigation\Decorators\MenuDecorator;

class DocsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMiddleware();

        $this->registerRoutes();

        $this->registerViews();

        $this->registerLocalizationView();
    }

    /**
     * Register middleware.
     *
     * @return void
     */
    public function registerMiddleware()
    {
        $this->app['router']->aliasMiddleware('locale', Locale::class);
    }

    /**
     * Register web routes.
     *
     * @return void
     */
    public function registerRoutes()
    {
        $prefixes = ['', '{lang?}'];

        foreach($prefixes as $prefix) {
            Route::middleware(['web', 'locale'])
                ->prefix($prefix)
                ->group(function () {
                    Route::name('laravelayers.')->prefix('laravelayers')->group(function () {
                        Route::get('docs/{doc}', '\Laravelayers\Docs\Controllers\IndexController@show')
                            ->name('docs.show');

                        Route::post('docs/{doc}', '\Laravelayers\Docs\Controllers\IndexController@store');

                        Route::get('search/docs', '\Laravelayers\Docs\Controllers\IndexController@search')
                            ->name('search.docs');

                        Route::get('docs/images/{image}', '\Laravelayers\Docs\Controllers\IndexController@image')
                            ->name('docs.images.show');

                        Route::get('docs', '\Laravelayers\Docs\Controllers\IndexController@index')
                            ->name('docs.index');
                    });
                });
        }
    }

    /**
     * Register the views.
     *
     * @return void
     */
    public function registerViews()
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'docs');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/resources/views/' => resource_path('views/vendor/docs'),
            ], 'laravelayers-docs');
        }
    }

    /**
     * Register the view for the localization menu.
     *
     * @return void
     */
    public function registerLocalizationView()
    {
        view()->composer(['docs::layouts.topBar'], function ($view) {
            $menu = collect([]);

            $parameters = request()->route()->parameters();

            unset($parameters['lang']);

            $dir = __DIR__ . '/readme/';

            foreach (scandir($dir) as $name) {
                if (is_dir($dir . $name) && $name != '.' && $name != '..') {
                    $menu[] = [
                        'id' => $name,
                        'name' => strtoupper($name),
                        'url' => route(request()->route()->getName(), array_merge(
                            $parameters,
                            ['lang' => $name]
                        )),
                        'parent_id' => App::isLocale($name) ? 0 : App::getLocale()
                    ];
                }
            }

            $view->with('langs', MenuDecorator::make($menu)->getMenu());
        });
    }
}
