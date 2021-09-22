<?php

namespace App\Http\Middleware;

use Closure;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //set locale
        if ($locale = $this->parseLocale($request)) {
            app()->setLocale($locale);
        }

        //next
        return $next($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return string|null
     */
    protected function parseLocale($request)
    {
        //set vars
        $locales = config('app.locales');
        $locale = $request->server('HTTP_ACCEPT_LANGUAGE');
        $locale = substr($locale, 0, strpos($locale, ',') ?: strlen($locale));

        //result - locale
        if (array_key_exists($locale, $locales)) {
            return $locale;
        }
        if (array_key_exists($locale = substr($locale, 0, 2), $locales)) {
            return $locale;
        }
    }
}
