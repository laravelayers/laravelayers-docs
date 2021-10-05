<?php

namespace Laravelayers\Docs\Middleware;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param null $name
     * @return mixed
     */
    public function handle($request, Closure $next, $name = null)
    {
        $segment = $request->segment(1);

        if ($segment !== 'laravelayers') {
            if ($segment == App::getLocale() || !file_exists(__DIR__ . '/../readme/' . $segment)) {
                session()->forget('locale');

                $segments = $request->segments();

                array_shift($segments);

                return redirect()->to(implode('/', $segments));
            }

            session(['locale' => $segment]);

            App::setLocale($segment);
        } else {
            if (session('locale') && session('locale') != App::getLocale()) {
                return redirect()->to(implode('/', Arr::prepend(
                    $request->segments(), session('locale')))
                );
            }
        }

        return $next($request);
    }
}
