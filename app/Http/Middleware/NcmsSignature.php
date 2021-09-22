<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class NcmsSignature
{
    /**
     * NCMS Signature.
     *
     * @var string
     */
    protected $signature = '<!-- NCMS Application | By Martin Thuku (20210521) -->';

    /**
     * Apply NCMS signature to response content.
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

        //modify response content - text/html
        if ($response instanceof Response && strpos((string) $response->headers->get('Content-Type'), 'text/html') !== false) {
            //get signature - set global ncms-signature
            x_globals_set('ncms-signature', $signature = $this->signature);

            //update response content - prepend signature
            $content = trim($response->getContent());
            $content = $signature . "\r\n" . $content;
            $response->setContent($content);
        }

        //response
        return $response;
    }
}
