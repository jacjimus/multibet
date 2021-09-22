<?php

namespace App\Http\Middleware;

use Closure;

class FsPremium
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
        //set user premium
        $us = app('UserService');
        $user = $us->getUser();
        $user_premium = $user ? $us->getUserPremium($user->id) : [];

        //view share
        view()->share('user_premium', $user_premium);

        //next
        return $next($request);
    }
}
