<?php

//$GLOBALS set
function x_globals_set($key, $value)
{
    if (!x_is_alphan($key, 1)) {
        throw new Exception(sprintf('Invalid global value key! (%s)', $key));
    }

    return $GLOBALS[$key] = $value;
}

//$GLOBALS get
function x_globals_get($key, $default=null)
{
    if (!x_is_alphan($key, 1)) {
        throw new Exception(sprintf('Invalid global value key! (%s)', $key));
    }

    return x_has_key($GLOBALS, $key) ? $GLOBALS[$key] : $default;
}

//$GLOBALS unset
function x_globals_unset($key)
{
    if (!x_is_alphan($key, 1)) {
        throw new Exception(sprintf('Invalid global value key! (%s)', $key));
    }
    if (x_has_key($GLOBALS, $key)) {
        unset($GLOBALS[$key]);
    }
}

//password hash
function x_pass_hash(string $str)
{
    return app()->make('UserService')->passHash($str);
}

//run artisan command
function x_artisan(string $cmd)
{
    //result ['cmd' => '', 'exit' => 0, 'output' => '']
    return app()->make('ConsoleService')->artisan($cmd);
}

//throw exception
function x_throw($err, int $err_no=0)
{
    throw $err instanceof Exception ? $err : new Exception($err, $err_no);
}

//time callback
function x_callback_time($callback, &$eta=null)
{
    if (!is_callable($callback)) {
        throw new Exception('Time callback is not callable!');
    }
    $eta = 0;
    $start = time();
    $result = $callback();
    $eta = time() - $start;

    return $result;
}
