<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class NcmsWeb
{
    /**
     * @var bool Enable html modifications.
     */
    protected $modify_html = true;

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

        //modify response content - text/html
        if ($this->modify_html && $response instanceof Response && strpos((string) $response->headers->get('Content-Type'), 'text/html') !== false) {
            $content = $response->getContent();
            $content = $this->modifyHtml($content);
            $response->setContent($content);
        }

        //response
        return $response;
    }

    /**
     * Modify response HTML.
     *
     * @param string $html
     *
     * @return string
     */
    public function modifyHtml(string $html)
    {
        //set vars
        $val = trim($html);
        $build = config('app.build');

        //css assets - set build number
        $pattern = '/<link[^<>]*href\="(' . preg_quote(asset('/'), '/') . '[^"]*\.css)"/';
        $val = preg_replace_callback($pattern, function ($matches) use (&$build) {
            //matched string
            $str = $matches[0];

            //ignore lib assets
            if (strpos($str, '/lib/') !== false) {
                return $str;
            }

            //ignore .min.css assets
            if (strpos($str, '.min.css') !== false) {
                return $str;
            }

            //set build number
            $url = $matches[1];
            $str = str_replace($url, "$url?v=$build", $str);

            return $str;
        }, $val);

        //javascript assets - set build number
        $pattern = '/<script[^<>]*src\="(' . preg_quote(asset('/'), '/') . '[^"]*\.js)"/';
        $val = preg_replace_callback($pattern, function ($matches) use (&$build) {
            //matched string
            $str = $matches[0];

            //ignore lib assets
            if (strpos($str, '/lib/') !== false) {
                return $str;
            }

            //ignore .min.js assets
            if (strpos($str, '.min.js') !== false) {
                return $str;
            }

            //set build number
            $url = $matches[1];
            $str = str_replace($url, "$url?v=$build", $str);

            return $str;
        }, $val);

        //image assets - set build number
        $pattern = '/<img[^<>]*src\="(' . preg_quote(asset('/'), '/') . '[^"]*)"/';
        $val = preg_replace_callback($pattern, function ($matches) use (&$build) {
            //matched string
            $str = $matches[0];

            //set build number
            $url = $matches[1];
            $str = str_replace($url, "$url?v=$build", $str);

            return $str;
        }, $val);

        //result
        return $val;
    }
}
