<?php

//to number
function x_num($val, bool $parse=false, $default=0)
{
    if (is_string($val) && (is_numeric($val) || $parse)) {
        $val = trim(preg_replace('/[^-0-9\.]/', '', $val));
    }

    return is_numeric($val) ? ($val * 1) : $default;
}

//to int
function x_int($val, bool $parse=false, $default=0)
{
    return !is_null($num = x_num($val, $parse, null)) ? (int) $num : $default;
}

//to abs int
function x_aint($val, bool $parse=false, $default=0)
{
    return !is_null($num = x_num($val, $parse, null)) ? abs((int) $num) : $default;
}

//to float
function x_float($val, bool $parse=false, $default=0)
{
    return !is_null($num = x_num($val, $parse, null)) ? (float) $num : $default;
}

//to abs float
function x_afloat($val, bool $parse=false, $default=0)
{
    return !is_null($num = x_num($val, $parse, null)) ? abs((float) $num) : $default;
}

//is integer
function x_is_int($val, bool $parse=false, &$num = 0)
{
    $num = x_int($val, $parse, null);

    return is_integer($num);
}

//is float
function x_is_float($val, bool $parse=false, &$num=0)
{
    $num = x_float($val, $parse, null);

    return is_float($num);
}
