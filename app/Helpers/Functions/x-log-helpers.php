<?php

use Illuminate\Support\Facades\Log;

//verbose start
function x_verbose_start($command=null)
{
    x_globals_set('x-verbose', $command);
}

//verbose stop
function x_verbose_stop()
{
    x_globals_unset('x-verbose');
}

//log
function x_log(...$args)
{
    Log::debug(...$args);
}

//globals verbose
function x_dump(...$items)
{
    //ignore if verbose is undefined
    if (!($class = x_globals_get('x-verbose'))) {
        return;
    }

    //handle/dump items
    array_map(function ($item) use (&$class) {
        if (
            x_is_alphan($item)
            && is_object($class)
            && method_exists($class, 'line')
        ) {
            return $class->line($item);
        }

        return dump($item);
    }, $items);
}

//log dump
function x_log_dump(string $log, string $name, $data, int $flags=FILE_APPEND)
{
    //log buffer
    $buffer = [];
    $buffer[] = now()->format('Y-m-d H:i:s') . ": $name";
    $buffer[] = '-------------------------------------------------------------------------';
    $buffer[] = trim(print_r($data, 1));
    $buffer[] = '-------------------------------------------------------------------------';
    $buffer[] = PHP_EOL;
    $buffer[] = PHP_EOL;

    //log content
    $content = x_join($buffer, PHP_EOL);

    //log file
    $path = storage_path() . '/logs/' . $log . '.log';

    //append log contents to file
    x_file_put($path, $content, $flags);
}

//dump val
function x_dump_val($val, &$key=null)
{
    if (is_array($val)) {
        $key = "$key - array";
        if (empty($val)) {
            return '[]';
        }
        $tmp = [];
        foreach ($val as $k => $v) {
            $v = x_dump_val($v, $k);
            $tmp[$k] = $v;
        }

        return $tmp;
    } elseif (is_object($val)) {
        $key = $key . ' - ' . get_class($val);

        return method_exists($val, 'toArray') ? $val->toArray() : '{}';
    }
    $key .= ($tmp = gettype($val)) ? ' - ' . $tmp : '';

    return x_str($val, 0, 1);
}
