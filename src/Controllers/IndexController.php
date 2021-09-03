<?php

namespace Laravelayers\Docs\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Laravelayers\Admin\Controllers\Controller as AdminController;
use Laravelayers\Docs\Decorators\FormsDecorator;
use Laravelayers\Docs\Services\DocsService;
use Laravelayers\Foundation\Decorators\Decorator;

class IndexController extends AdminController
{
    /**
     * Docs menu.
     *
     * @var array|Decorator
     */
    protected static $docsMenu = [];

    /**
     * Create a new IndexController instance.
     *
     * @param DocsService $docsService
     */
    public function __construct(DocsService $docsService)
    {
        if (!App::environment('local')) {
            $this->middleware('can:admin.*');
        }

        $this->service = $docsService;
    }

    /**
     * Initialize items for the admin menu bar.
     *
     * @return array
     */
    protected function initMenu()
    {
        return [
            'route' => 'laravelayers.docs.index',
            'url' => route('laravelayers.docs.show', ['', 'admin']),
            'name' => trans('admin::admin.menu.docs'),
            'sorting' => '9999',
            'icon' => 'icon-file-alt',
            'label' => 'docs',
            'class' => 'alert'
        ];
    }

    /**
     * Display main page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $menu = $this->getDocsMenu();

        $content = $this->service->getFile('readme');

        return view('docs::index', compact('menu', 'content'));
    }

    /**
     * Display the specified docs file.
     *
     * @param Request $request
     * @return \Illuminate\View\View|RedirectResponse|string
     */
    public function show(Request $request)
    {
        if ($request->ajax()) {
            return (string) FormsDecorator::make()->getElementTextareaImages();
        }

        $content = $this->service->getFile($request->route('doc'));

        if ($content instanceof RedirectResponse) {
            return $content;
        }

        abort_if(!$content, 404);

        $menu = $this->getDocsMenu();

        return view('docs::index', compact('menu', 'content'));
    }

    /**
     * Update the specified resource in the repository.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        FormsDecorator::make()->getElements()->validate();

        return redirect()->to(url()->current() . '?checklist=1');
    }

    /**
     * Display the specified docs file.
     *
     * @param Request $request
     * @return \Illuminate\View\View|string
     * @throws \Throwable
     */
    public function search(Request $request)
    {
        $content = $this->service->searchFiles($request, 3, 10);

        if ($request->ajax()) {
            return $content;
        }

        $title = trans('admin::admin.menu.search');

        $menu = $this->getDocsMenu();

        return view('docs::index', compact('title', 'menu', 'content'));
    }

    /**
     * Display the specified image.
     *
     * @param Request $request
     */
    public function image(Request $request)
    {
        $this->service->getImage($request);
    }

    /**
     * Get the docs menu.
     *
     * @param string $key
     * @return array|Decorator
     */
    public function getDocsMenu($key = '')
    {
        if (!static::$docsMenu) {
            $this->getMenu();

            $menu = static::$menu;

            static::$menu = [];

            static::$docsMenu = null;

            static::$docsMenu = $this->getMenu($key);

            static::$menu = $menu;
        }

        static::$docsMenu->put('title', static::$docsMenu->get('path')->getTitle(
            $this->service->getFileHeading('readme')
        ));

        return static::$docsMenu;
    }

    /**
     * Get the menu item for the docs menu bar.
     *
     * @return array
     */
    public function getMenuItem()
    {
        if (!static::$menu && !is_null(static::$docsMenu)) {
            return parent::getMenuItem();
        }

        return $this->getMenuCache()->where('route', request()->doc)->first();
    }

    /**
     * Add action method to the docs path.
     *
     * @return bool
     */
    protected function addActionToMenuPath()
    {
        return false;
    }

    /**
     * Get menu cache.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getMenuCache()
    {
        if (!static::$menu && !is_null(static::$docsMenu)) {
            return parent::getMenuCache();
        }

        $menu = $this->service->getFileLinks();

        foreach($menu as $key => $item) {
            $menu[$key] = $this->prepareMenuItem($item);
        }

        return collect($menu);
    }
}
