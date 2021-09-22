<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

//get uuid
function x_uuid(bool $timestamp=true)
{
    $str = Str::uuid();
    if ($timestamp) {
        $arr = array_slice(explode('-', $str), 3);
        $str = now()->format('Y-m-d-His') . '-' . rand(0, 9) . implode('-', $arr);
    }

    return $str;
}

//convert $val to string
function x_str($val, bool $trim=false, bool $include_non_alphan=false)
{
    if (is_null($val) || $val === '') {
        $str = '';
    } elseif (is_string($val)) {
        $str = $trim ? trim($val) : $val;
    } elseif (is_numeric($val)) {
        $str = "$val";
    } elseif (!$include_non_alphan) {
        $str = '';
    } elseif ($val instanceof Exception) {
        $str = x_err($val);
    } else {
        $str = json_encode($val);
    }

    return x_eol($str);
}

//trim alphanumeric string
function x_tstr($val)
{
    return x_str($val, 1);
}

//exception error to string
function x_err($err, int $err_no=0)
{
    $t = '    ';
    $eol = PHP_EOL;
    $buffer = [];
    if (is_object($err) && $err instanceof \Throwable) {
        //message
        $class = get_class($err);
        $code = $err->getCode();
        $message = $err->getMessage();
        $buffer[] = trim("$class [$code]: $message");

        //file line
        $base_path = x_base_path();
        $__file = function ($str) use (&$base_path) {
            return trim(str_replace($base_path, '', $str));
        };
        $file = $__file($err->getFile());
        $line = $err->getLine();
        $buffer[] = ' - ' . str_pad("[$line]", strlen('[0000]')) . " $file";
        dump($err->getTraceAsString());

        //stack trace
        if (is_array($trace = $err->getTrace())) {
            $count = count($trace);
            foreach ($trace as $i => $item) {
                //trace vars
                $file = isset($item['file']) && ($tmp = trim($item['file'])) ? $__file($tmp) : null;
                $line = isset($item['line']) ? (int) $item['line'] : null;
                $class = isset($item['class']) && ($tmp = trim($item['class'])) ? $tmp : null;
                $function = isset($item['function']) && ($tmp = trim($item['function'])) ? $tmp : null;
                $args = isset($item['args']) && is_array($tmp = $item['args']) ? $tmp : [];

                //buffer trace line & file
                if ($file) {
                    $buffer[] = ' - ' . str_pad("[$line]", strlen('[000]')) . " $file";
                } else {
                    continue;
                }

                //buffer ignore trace method if $i > 2
                if ($i > 2) {
                    continue;
                }

                //buffer trace method
                if ($function) {
                    $method = $t . $t . '  ';
                    if ($class) {
                        $method .= $class . '::';
                    }
                    $method .= "$function(";
                    if ($tmp = count($args)) {
                        $method .= "args[$tmp]";
                    }
                    $method .= ')';
                    $buffer[] = $method;
                }
            }
        }
    } else {
        $buffer[] = ($err_no ? "Error [$err_no]: " : '') . trim((string) $err);
    }

    //result - error buffer string
    return x_join($buffer, $eol);
}

//trim characters
function x_trim_characters(string $chars=null, bool $default_chars=true)
{
    //default trim characters
    $characters = $default_chars ? " \n\r\t\v\0" : null;

    //combine $chars trim characters
    if (!is_null($chars)) {
        $tmp = (is_null($characters) ? '' : $characters) . ((string) $chars);
        $tmp = implode('', array_unique(str_split($tmp))); //unique characters
        $characters = $tmp;
    }

    //result - trim characters
    return $characters;
}

//trim string
function x_trim($val, string $chars=null, bool $default_chars=true)
{
    return call_user_func('trim', ...array_merge([$val], is_null($chars = x_trim_characters($chars, $default_chars)) ? [] : [$chars]));
}

//ltrim string
function x_ltrim($val, string $chars=null, bool $default_chars=true)
{
    return call_user_func('ltrim', ...array_merge([$val], is_null($chars = x_trim_characters($chars, $default_chars)) ? [] : [$chars]));
}

//rtrim string
function x_rtrim($val, string $chars=null, bool $default_chars=true)
{
    return call_user_func('ltrim', ...array_merge([$val], is_null($chars = x_trim_characters($chars, $default_chars)) ? [] : [$chars]));
}

//convert $str to singular (examples = example)
function x_singular(string $str)
{
    return Str::singular($str);
}

//convert $str to plural (example = examples)
function x_plural(string $str)
{
    return Str::plural($str);
}

//convert $str to studly case (ExampleString)
function x_studly(string $str, bool $force=false)
{
    //force case - normalize string case before convertion
    if ($force) {
        $str = Str::slug(Str::snake($str));
    }

    //result - string converted to studly case
    return Str::studly($str);
}

//convert $str to snake case (example_string)
function x_snake(string $str, bool $force=false)
{
    //force case - normalize string case before convertion
    if ($force) {
        $str = Str::studly(Str::slug(Str::snake($str)));
    }

    //result - string converted to snake case
    return Str::snake($str);
}

//convert $str to slug case (example-string)
function x_slug(string $str, bool $force=false)
{
    //force case - normalize string case before convertion
    if ($force) {
        $str = Str::snake($str);
    }

    //result - string converted to slug case
    return Str::slug($str);
}

//convert $val to json string
function x_to_json($val)
{
    return json_encode($val);
}

//check if value is string
function x_is_string($val, bool $not_empty=false, bool $empty_trim_string=true)
{
    return is_string($val) && ($not_empty ? strlen($empty_trim_string ? trim($val) : $val) : 1);
}

//check if value is alphanumeric (string/number)
function x_is_alphan($val, bool $not_empty=false, bool $empty_trim_string=true)
{
    return is_numeric($val) || x_is_string($val, $not_empty, $empty_trim_string);
}

//check if value is json string
function x_is_json($val, $assoc=true, &$decoded=null)
{
    return !is_null($decoded = json_decode($val, $assoc));
}

//quote string
function x_qstr($val, $quote="'", string $escape="\\'")
{
    $val = x_str($val);
    if (strpos($val, $quote) !== false) {
        $val = str_replace($escape, $quote, $val);
        $val = str_replace($quote, $escape, $val);
    }

    return $val;
}

//to php string
function x_php_str($val, int $max_length=0, string $indent=null)
{
    $max_length = is_integer($max_length) && $max_length >= 10 ? $max_length : 0;
    $t = "\t";
    $eol = PHP_EOL;

    //array/object
    if (is_array($val) || is_object($val)) {
        //to array
        $val = (array) $val;

        //empty array
        if (empty($val)) {
            return $indent . '[]';
        }

        //assoc array
        if (x_is_assoc($val)) {
            $lines = [];
            $lines[] = $indent . '[';
            foreach ($val as $key => $value) {
                $kstr = $indent . $t . "'" . x_qstr($key) . "' => ";
                $vstr = ltrim(x_php_str(
                    $value,
                    $max_length ? $max_length - strlen($kstr) : 0,
                    $indent . $t
                ));
                $lines[] = $kstr . $vstr . ',';
            }
            $lines[] = $indent . ']';

            return x_join($lines, $eol);
        }

        //list array
        else {
            $str = x_join(
                $val,
                $glue=', ',
                $template='%s',
                $max_length,
                $max_glue=',' . $eol . $indent . $t,
                $max_template=$max_glue . '%s' . $eol . $indent,
                $to_string=function ($item) use (&$max_length, &$indent, &$t) {
                    return x_php_str(
                        $item,
                        $max_length ? $max_length - strlen($indent . $t) : 0,
                        $indent . $t
                    );
                }
            );
            $str = ltrim($str, ',');

            return $indent . sprintf('[%s]', $str);
        }
    }

    //null
    elseif (is_null($val)) {
        return 'null';
    }

    //bool
    elseif (is_bool($val)) {
        return $val ? 'true' : 'false';
    }

    //number
    elseif (is_integer($val) || is_float($val) || is_double($val)) {
        return x_str($val);
    }

    //string
    return "'" . x_qstr($val) . "'";
}

//is url
function x_is_url($str)
{
    return filter_var($str, FILTER_VALIDATE_URL);
}

//is email
function x_is_email($str)
{
    return filter_var($str, FILTER_VALIDATE_EMAIL);
}

//is valid host
function x_is_valid_host($host)
{
    if (empty($host) || !is_string($host) || strlen($host) > 256) {
        return false;
    }
    if (trim($host, '[]') != $host) {
        return (bool) filter_var(trim($host, '[]'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }
    if (is_numeric(str_replace('.', '', $host))) {
        return (bool) filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    return (bool) filter_var('http://' . $host, FILTER_VALIDATE_URL);
}

//get server host name
function x_server_hostname($host=null)
{
    $result = '';
    if ($host = trim($host)) {
        $result = $host;
    } elseif ($url = trim(config('app.url'))) {
        $result = $url;
    } elseif (isset($_SERVER) and array_key_exists('SERVER_NAME', $_SERVER)) {
        $result = $_SERVER['SERVER_NAME'];
    } elseif (function_exists('gethostname') && ($tmp = gethostname()) !== false) {
        $result = $tmp;
    } elseif (($tmp = php_uname('n')) !== false) {
        $result = $tmp;
    }
    if (!x_is_valid_host($result)) {
        return 'localhost.localdomain';
    }
    $result = ($tmp = trim(parse_url($result, PHP_URL_HOST))) ? $tmp : $result;

    return $result;
}

//to detected type
function x_cast(
    $val,
    bool $trim_string=false,
    bool $json_decode=false,
    bool $json_assoc=true,
    int $decimal_places=2
) {
    if (is_numeric($val)) {
        $val = trim($val) * 1;
        if (is_float($val) || is_double($val)) {
            return is_integer($decimal_places) && $decimal_places > 0 ? round($val, $decimal_places) : $val;
        }

        return (int) $val;
    }
    if (is_string($val)) {
        if (in_array($tmp = trim($val), ['true', 'false'])) {
            return x_bool($tmp);
        }
        if ($json_decode && x_is_json($val, $json_assoc, $decoded)) {
            return $decoded;
        }
    }

    return x_str($val, $trim_string);
}

//to boolean
function x_bool($val)
{
    if (x_is_empty($val)) {
        return false;
    }
    if (is_string($val)) {
        $val = trim(strtolower($val));
        if ($val == 'true') {
            return true;
        }
        if ($val == 'false') {
            return false;
        }
    }

    return $val ? true : false;
}

//empty
function x_empty($val, $trim=true)
{
    if (is_bool($val) || is_numeric($val)) {
        return false;
    }
    if (is_string($val)) {
        return empty($trim ? trim($val) : $val);
    }
    if (is_object($val)) {
        return empty((array) $val);
    }

    return empty($val);
}

//isset - not empty
function x_isset(&$val, $trim=true, $bool=false)
{
    return isset($val) && !x_empty($val, $trim) && ($bool ? x_bool($val, $trim) : 1);
}

//isset - bool
function x_isset_b(&$val)
{
    return x_isset($val, 1, 1);
}

//encrypt string
function x_encrypt(string $str)
{
    return Crypt::encryptString($str);
}

//decrypt string
function x_decrypt(string $encrypted)
{
    try {
        $decrypted = Crypt::decryptString($encrypted);

        return $decrypted;
    } catch (DecryptException $e) {
        x_throw($e);
    }
}

//replace string EOL delimiter
function x_eol(string $str, string $eol=PHP_EOL)
{
    return str_replace("\n", $eol, str_replace(["\r", "\n"], "\n", (string) $str));
}

//trim all whitespace to one of each. i.e. "  " = " "
function x_str_space(string $str, $omit=null)
{
    $str = (string) $str;
    if (!is_null($omit) && count($omit = (array) $omit)) {
        foreach ($omit as $item) {
            $str = str_replace((string) $item, ' ', $str);
        }
    }
    $str = str_replace(["\r", "\n"], "\n", $str);
    $str = preg_replace('/[ ]+[\t]/', "\t", $str);
    $str = preg_replace('/[\t][ ]+/', "\t", $str);
    $str = preg_replace('/[\t]+/', "\t", $str);
    $str = preg_replace('/\s+[\n]/', "\n", $str);
    $str = preg_replace('/[\n]\s+/', "\n", $str);
    $str = preg_replace('/[\n]+/', "\n", $str);
    $str = preg_replace('/[ ]+/', ' ', $str);
    $str = str_replace("\n", PHP_EOL, $str);

    return trim($str);
}
