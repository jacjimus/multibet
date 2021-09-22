<?php

namespace App\Http\Middleware;

use Closure;

class NcmsModels
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
        //request path
        $path = trim($request->path());
        dump(['NcmsModels' => [
            'path' => $path,
            'input' => $request->input(),
        ]]);
        dump(['request' => $request]);

        //TODO...
        return $next($request);
    }
}
