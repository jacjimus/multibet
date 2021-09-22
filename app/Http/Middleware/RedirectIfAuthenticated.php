<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              ...$guards
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        //set guards
        $guards = empty($guards) ? [null] : $guards;

        //guards check
        foreach ($guards as $guard) {
            //check authentication
            if (Auth::guard($guard)->check()) {
                //authenticated - json response
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Already authenticated.'
                    ], 400);
                }

                //authenticated - redirect home
                else {
                    return redirect(RouteServiceProvider::HOME);
                }
            }
        }

        //next
        return $next($request);
    }
}
