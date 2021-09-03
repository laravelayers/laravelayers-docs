<?php

namespace Laravelayers\Docs\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Laravelayers\Docs\Decorators\FormsDecorator;
use Laravelayers\Foundation\Services\Service;
use Laravelayers\Navigation\Decorators\MenuDecorator;
use Parsedown;

class DocsService extends Service
{
    /**
     * Links to docs files.
     *
     * @var array
     */
    protected $fileLinks = [];

    /**
     * Get the content of the docs file.
     *
     * @param string $name
     * @return string|\Illuminate\Http\RedirectResponse|null
     */
    public function getFile($name)
    {
        if (Str::endsWith($name, '.md')) {
            return redirect(
                route('laravelayers.docs.show', [
                    request()->route('lang'),
                    substr($name, 0, -3)
                ])
            );
        }

        $file = $this->getFilePath($name);

        if (!$file) {
            return null;
        }

        return $this->prepareFileContent($file);
    }

    /**
     * Prepare the content of the docs file.
     *
     * @param string $file
     * @return string
     */
    protected function prepareFileContent($file)
    {
        $content = file_get_contents($file);

        $content = preg_replace('/```html([^`]*)```/s', '$1', $content);

        // Local links
        $content = preg_replace(
            '/\[([^\]]*)\]\(([^#|http][^)]*).md\)/Uis',
            '[$1](' . route('laravelayers.docs.index', [request()->route('lang')]) . '/$2)',
            $content
        );

        // Local images
        $content = preg_replace(
            '/!\[([^\]]*)\]\(([^#|http][^)]*)\/images\/([^)]*)\)/Uis',
            '![$1](' . route('laravelayers.docs.images.show', [request()->route('lang'), '']) . '/$3)',
            $content
        );

        $content = app(Parsedown::class)->text($content);

        // Tables
        $content = preg_replace(
            '/(<table[^>]*>)(.*)(<\/table>)/Uis',
            '<div class="table-scroll">$1$2$3</div>',
            $content
        );

        $content = FormsDecorator::make($content)->getContent();

        return $content;
    }

    /**
     * Get links to docs files.
     *
     * @return array
     */
    public function getFileLinks()
    {
        if (!$this->fileLinks) {
            $readmeFile = $this->getReadmeFile();

            foreach ($readmeFile['items'] as $name => $parent) {
                $i = $i ?? 1;

                $this->fileLinks[$i] = [
                    'route' => $name,
                    'name' => $readmeFile['texts'][$name] ?? $this->getFileHeading($name),
                    'url' => route('laravelayers.docs.show', [request()->route('lang'), $name]),
                    'parent' => $parent ? "_{$parent}" : '',
                    'sorting' => $i,
                    'hidden' => false
                ];

                if (in_array($name, $readmeFile['items'])) {
                    $parent = $name ? "_{$name}" : '';

                    $this->fileLinks[$i] = array_merge(array_last($this->fileLinks), [
                        'route' => $parent,
                        'parent' => '',
                        'sorting' => $i,
                    ]);

                    if (!Str::contains($name, '#')) {
                        $i++;

                        $this->fileLinks[$i] = array_merge(array_last($this->fileLinks), [
                            'route' => $name,
                            'parent' => $parent,
                            'sorting' => $i,
                        ]);
                    } else {
                        $this->fileLinks[$i]['url'] = str_replace('/#', '/', $this->fileLinks[$i]['url']);
                    }
                }

                $i++;
            }
        }

        return $this->fileLinks;
    }

    /**
     * Get the data from the readme file.
     *
     * @return array
     */
    protected function getReadmeFile()
    {
        $items = [];
        $texts = [];

        if ($file = $this->getFilePath('readme')) {
            $file = fopen($this->getFilePath('readme'), 'r');

            while (($line = fgets($file, 4096)) !== false) {
                $isItem = Str::startsWith($line, '-');
                $isSubItem = preg_match('/^[\t|\s]+\-/', $line);

                if ($isItem || $isSubItem) {
                    $text = trim(substr(
                        substr($line, 0, strpos($line, ']')), strpos($line, '[') + 1
                    ));

                    $name = preg_replace('/\.md$/i', '', trim(substr(
                        substr($line, 0, strpos($line, ')')), strpos($line, '(') + 1
                    )));

                    if ($text) {
                        $parent = $parent ?? '';

                        if ($isItem) {
                            $parent = '';
                        }

                        if ($isSubItem) {
                            $parent = $parent ?: ($last ?? '');
                        }

                        $last = $name;

                        $items[$name] = $parent ?? '';

                        $texts[$name] = $text;
                    }
                }
            }

            fclose($file);
        }

        if (!$items) {
            $items = $this->getFileNames();
        }

        return compact('items', 'texts');
    }

    /**
     * Search for docs files that contain the specified text.
     *
     * @param Request $request
     * @param int $length
     * @param int $rows
     * @return \Illuminate\Support\HtmlString|string
     * @throws \Throwable
     */
    public function searchFiles(Request $request, $length = 3, $rows = 10)
    {
        $text = $request->get('text');

        if (mb_strlen($text) <= $length) {
            return '';
        }

        $text = mb_strtolower($text);

        if (mb_strlen($text) > 7) {
            $truncated = mb_substr($text, 0, -2);
        }

        $files = $this->getFileLinks();

        foreach ($files as $key => $value) {
            $file = $this->getFilePath($value['route']);

            if (file_exists($file)) {
                $content = mb_strtolower(file_get_contents($file));

                $count = mb_substr_count($content, $text);

                if (!empty($truncated)) {
                    $count += mb_substr_count($content, $truncated);
                }


                if ($count) {
                    $result[$key] = array_merge($files[$key], [
                        'name' => "{$value['name']} ({$count})"
                    ]);

                    if ($request->ajax() && count($result) >= $rows) {
                        break;
                    }
                }
            }
        }

        return MenuDecorator::make(collect($result ?? []))->getMenu()->render('vertical');
    }

    /**
     * Get the image.
     *
     * @param Request $request
     */
    public function getImage(Request $request)
    {
        $file = __DIR__ . '/../storage/images/' . $request->route('image');

        if (!file_exists($file)) {
            $file = $this->getDirName() . 'images/' . $request->route('image');

            if (!file_exists($file)) {
                $file = $this->getDirName(true) . 'images/' . $request->route('image');

                if (!file_exists($file)) {
                    abort(404);
                }
            }
        }

        $mime = mime_content_type($file);

        if (strpos($mime, 'image/svg') !== false) {
            $mime = 'image/svg+xml';
        }

        header("Content-type: {$mime}");

        echo file_get_contents($file);

        exit;
    }

    /**
     * Get the first level heading from the docs file.
     *
     * @param string $file
     * @return null|string
     */
    public function getFileHeading($file)
    {
        $file = $this->getFilePath($file);

        if (!$file) {
            return null;
        }

        $f = fopen($file, 'r');

        $heading = fgets($f);

        fclose($f);

        return trim(str_replace('#', '', $heading));
    }

    /**
     * Get the docs file path.
     *
     * @param string $name
     * @return null|string
     */
    protected function getFilePath($name)
    {
        $name = preg_replace( '/\.md$/i', '', $name) . '.md';

        $file = $this->getDirName() . $name;

        if (!file_exists($file)) {
            $file = $this->getDirName(true) . $name;

            if (!file_exists($file)) {
                return null;
            }
        }

        return $file;
    }

    /**
     * Get the docs file names.
     *
     * @return array
     */
    protected function getFileNames()
    {
        $files = [];

        foreach(scandir($this->getDirName()) as $key => $file) {
            if (Str::endsWith($file, '.md')) {
                $file = preg_replace('/\.md$/i', '', $file);

                $files[$file] = '';
            }
        };

        return $files;
    }

    /**
     * Get the name of the directory with the docs files.
     *
     * @param bool $default
     * @return string
     */
    protected function getDirName($default = false)
    {
        $dir = __DIR__ . '/../readme/';

        $localeDir = $dir . session('locale', App::getLocale());

        if ($default || !file_exists($localeDir)) {
            $localeDir = $dir . 'ru';
        }

        return $localeDir . '/';
    }
}
