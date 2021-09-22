<?php

namespace App\Http\Middleware;

use Closure;

class Access
{
    /**
     * Process incoming request access.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //view share
        view()->share('app_user', app()->make('UserService')->getUser());
        view()->share('app_locale', app()->getLocale());

        #TODO: enforce access permissions

        //next
        return $next($request);
    }
}
