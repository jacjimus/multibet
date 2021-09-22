<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

/* incomplete */
class NcmsMinify
{
    /**
     * Minify html response content.
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
            $content = $response->getContent();
            $content = $this->minify($content, 1);
            $response->setContent($content);
        }

        //response
        return $response;
    }

    /**
     * Minify HTML.
     *
     * @param string $html
     *
     * @return string
     */
    public function minify(string $html, bool $comments=false, string $eol="\r\n")
    {
        //set val
        $val = trim($html);

        //save <<<sig>>>
        $sig = trim(x_globals_get('ncms-signature', ''));
        if ($sig) {
            $val = str_replace($sig, '<<<sig>>>', $val);
        }

        //remove tab space
        $val = str_replace("\t", '', $val);

        //save <<<eol>>>
        $val = str_replace(["\r", "\n"], $n = '<<<eol>>>', $val);

        //trim whitespace
        $val = preg_replace("/$n\s*$n\s*/", $n, $val);
        $val = preg_replace("/$n\s+/", $n, $val);
        $val = preg_replace("/$n($n)+/", $n, $val);

        /*
        $val = preg_replace_callback("/<a(.*?(?=>))>$n((.*?(?=$n<\/a))$n(<\/a))/i", function($matches) use (&$n) {
            $str = '<a' . $matches[1] . '>';
            if ($tmp = $matches[2][0] == '<') $str .= $n;
            $str .= $matches[3];
            if ($tmp) $str .= $n;
            $str .= $matches[4];
            return $matches[2][0] == '<' ? $str : str_replace($n, '', $str);
        }, $val);
        */
        $val = preg_replace("/<a(.*?(?=>))>$n/i", '<a$1>', $val);
        $val = preg_replace("/$n<\/a/i", '</a', $val);

        $val = preg_replace('/\s\s+/', ' ', $val);

        //restore <<<eol>>>
        $val = str_replace($n, $eol, $val);

        //comments
        if ($comments) {
            $val = preg_replace_callback('/(^|\n)(<\!--(.*?(?=-->)))-->/', function ($matches) {
                return stripos($matches[2], '/') !== false ? "{$matches[1]}{$matches[2]}-->\r\n" : "{$matches[1]}\r\n{$matches[2]}-->";
            }, $val);
            $val = preg_replace('/-->\s*<\!--/', "-->\r\n<!--", $val);
        } else {
            //remove comments
            $val = preg_replace_callback('#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s', function ($matches) use ($eol) {
                if (strpos($tmp = $matches[0], '<!--') === false) {
                    return $tmp;
                }

                return $eol != '' && strpos($tmp, $eol) !== false ? $eol : '';
            }, $val);

            //trim comments whitespace
            $val = preg_replace('/\n\s*\r?\n/', "\n", $val);
        }

        //restore <<<sig>>>
        if ($sig) {
            $val = str_replace('<<<sig>>>', $sig . "\r\n", $val);
        }

        //result
        return $val;
    }
}
