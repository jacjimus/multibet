<?php

namespace App\Http\Middleware;

use Closure;

class Cleanup
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
        //get response
        $response = $next($request);

        //cleanup after response
        $this->cleanup();

        //return response
        return $response;
    }

    /**
     * Request cleanup.
     *
     * @return void
     */
    public function cleanup()
    {
        //upload service
        app('UploadService')->cleanup();

        //mpesa service
        app('App\Services\MpesaService')->cleanup();
    }
}
