<?php

//check if value is empty
function x_is_empty($val, bool $trim_string=true)
{
    if (is_bool($val) || is_numeric($val)) {
        return false;
    }
    if (is_string($val)) {
        return empty($trim_string ? trim($val) : $val);
    }
    if (is_object($val)) {
        return empty((array) $val);
    }

    return empty($val);
}

//is object (including arrays)
function x_is_object($val, bool $include_arrays=true)
{
    return is_object($val) || $include_arrays ? is_array($val) : 1;
}

//check if array is associative
function x_is_assoc($arr)
{
    return is_array($arr) && !empty($arr) && ($c = count($arr)) && array_keys($arr) !== range(0, $c - 1);
}

//check if array is list (not associative)
function x_is_list($arr, bool $include_empty_array=true)
{
    return is_array($arr) && !x_is_assoc($arr) && ($include_empty_array ? 1 : !empty($arr));
}

//convert value to array (if not already an array)
function x_to_array($val, bool $include_empty=false, bool $trim_string=true)
{
    if (is_string($val) && $trim_string) {
        $val = trim($val);
    }
    if (x_is_empty($val, 1) && !$include_empty) {
        return [];
    }

    return (array) $val;
}

//to array
function x_arr($val, bool $inset=false, bool $include_empty=false, bool $trim_string=true)
{
    return !is_array($val) && !$inset ? [] : x_to_array($val, $include_empty, $trim_string);
}

//convert to array list
function x_to_list($val, bool $include_empty=false, bool $trim_string=true, string $split_string=null)
{
    //split string value
    if (is_string($val) && x_is_string($split_string, 1)) {
        $val = x_split($split_string, $val, $c, 1, $trim_string);
    }

    //to array
    return array_values(x_to_array($val, $include_empty, $trim_string));
}

//normalize keys
function x_array_norm_keys(&$val, array $map)
{
    //ignore invalid map
    if (!(is_array($map) && !empty($map))) {
        return $val;
    }

    //set val keys - ignore invalid
    if (!(is_array($val) && count($keys = array_keys($val)))) {
        return $val;
    }

    //keys - lowercase
    $keys_lower = array_map(function ($key) {
        return strtolower($key);
    }, $keys);

    //normalize keys
    $is_list = x_is_list($map);
    foreach ($map as $key => $norm_key) {
        //set key, pos - ignore missing
        $key = $is_list ? $norm_key : $key;
        if (($pos = array_search(strtolower($key), $keys_lower)) === false) {
            continue;
        }

        //set val key
        $vkey = $keys[$pos];

        //ignore no change
        if (trim($vkey) === trim($norm_key)) {
            continue;
        }

        //set key value - unset key
        $value = $val[$vkey];
        unset($val[$vkey]);

        //set val normalized
        $val = array_merge(
            array_slice($val, 0, $pos, true),
            [$norm_key => $value],
            array_slice($val, $pos, null, true)
        );
    }

    //result - normalized val
    return $val;
}

//check if object has key
function x_has_key($val, $key)
{
    //check if array has key
    if (is_array($val)) {
        return array_key_exists($key, $val);
    }

    //check if object has property
    if (is_object($val)) {
        return property_exists($val, $key);
    }

    //return false (key/property not found)
    return false;
}

//check if object has keys
function x_has($val, ...$keys)
{
    //check if val is array|object - not empty
    if (!(x_is_object($val, 1) && !empty($val))) {
        return false;
    }

    //check if all keys exists
    foreach ($keys as $key) {
        //if key not in value return false
        if (!x_has_key($val, $key)) {
            return false;
        }
    }

    //return true (all keys exist)
    return true;
}

//array remove empty values
function x_array_remove_empty($arr, bool $trim_string=true)
{
    $buffer = [];
    if (is_array($arr) && !empty($arr)) {
        foreach ($arr as $key => $val) {
            if (x_is_empty($val, $trim_string)) {
                continue;
            }
            if (is_string($val) && $trim_string) {
                $val = trim($val);
            }
            $buffer[$key] = $val;
        }
    }

    return x_is_assoc($arr) ? $buffer : array_values($buffer);
}

//split string to array (sets $count)
function x_split($glue, $str, &$count=0, bool $remove_empty=false, bool $trim_string=false)
{
    $glue = x_str($glue);
    $arr = !strlen($str = x_str($str, $trim_string)) ? [] : (!strlen($glue) ? str_split($str) : explode($glue, $str));
    if ($remove_empty) {
        $arr = x_array_remove_empty($arr, $trim_string);
    }
    $count = count($arr);

    return $arr;
}

//split and pluck empty
function x_tsplit($glue, $str, &$count=0)
{
    $count = count($items = x_split($glue, $str, $c, 1, 1));

    return $items;
}

//join array
function x_join(
    array $arr,
    string $glue='',
    string $template='%s',
    int $max_length=0,
    string $max_glue='',
    string $max_template='%s',
    $to_string=null
) {
    //join delimiter & template (default '' & '%s')
    $glue = x_str($glue);
    $template = $template && strpos($template, '%s') !== false ? $template : '%s';

    //max join delimiter & template (default '' & '%s')
    $max_glue = x_str($max_glue);
    $max_template = $max_template && strpos($max_template, '%s') !== false ? $max_template : '%s';

    //join method
    $__join = function (&$items, $is_max_items=false) use (&$glue, &$template, &$max_glue, &$max_template, &$to_string, &$__join) {
        if (is_callable($to_string) && is_array($items)) {
            $items = array_map(function ($item) use (&$to_string) {
                return is_array($item) ? $item : $to_string($item);
            }, $items);
        }
        if (!$is_max_items) {
            return sprintf($template, implode($glue, $items));
        }
        $buffer = [];
        foreach ($items as $items_list) {
            $buffer[] = $__join($items_list);
        }
        $str = sprintf($max_template, implode($max_glue, $buffer));
        $str = preg_replace('/(\s*)$/', rtrim($max_glue) . '$1', $str);
        $str = preg_replace('/' . preg_quote(trim($max_glue)) . '(\s*)$/', '$1', $str);

        return $str;
    };

    //join array items
    $items = [];
    $max_items = [];
    foreach (x_to_list($arr) as $item) {
        $items[] = $item;
        if ($max_length > 0 && strlen($max_glue . sprintf($template, implode($glue, $items))) >= $max_length) {
            $max_items[] = $items;
            $items = [];
        }
    }
    if (!empty($max_items) && !empty($items)) {
        $max_items[] = $items;
    }
    if (count($max_items) == 1) {
        $items = $max_items[0];
        $max_items = [];
    }

    //result - joined items
    return empty($max_items) ? $__join($items) : $__join($max_items, true);
}

//end
function x_end(array $arr)
{
    return is_array($arr) ? end($arr) : null;
}

//merge array items
function x_merge(...$items)
{
    $buffer = [];
    foreach ($items as $item) {
        if (x_is_empty($item)) {
            continue;
        }
        if (x_is_assoc($item)) {
            foreach ($item as $key => $value) {
                if (x_is_empty($value)) {
                    continue;
                }
                if (array_key_exists($key, $buffer) && !x_is_empty($buffer_value = $buffer[$key])) {
                    if (is_array($buffer_value) || is_array($value)) {
                        $value = x_merge($buffer_value, $value);
                    }
                }
                $buffer[$key] = $value;
            }
        } elseif (x_is_list($item, 0)) {
            foreach ($item as $value) {
                if (!in_array($value, $buffer)) {
                    $buffer[] = $value;
                }
            }
        } elseif (!in_array($item, $buffer)) {
            $buffer[] = $item;
        }
    }

    return $buffer;
}

//trim list array
#deprecated - use x_array_remove_empty instead
function x_pluck_empty(array $arr, bool $trim_string=true)
{
    //ignore invalid list
    if (!x_is_list($arr)) {
        return $arr;
    }

    //buffer
    $buffer = [];
    foreach ($arr as $item) {
        if (is_string($item) && $trim_string) {
            $item = trim($item);
        }
        if (x_is_empty($item, 0)) {
            continue;
        }
        $buffer[] = $item;
    }

    //result - cleaned buffer
    return $buffer;
}

//array resolve path value
function x_array_get($path, $arr, $default=null, bool $throw_missing=false)
{
    $val = x_arr($arr);
    if (empty($keys = x_split('.', $path, $count, $remove_empty=1))) {
        return $val;
    }
    foreach ($keys as $i => $key) {
        if (!x_has_key($val, $key)) {
            if ($throw_missing) {
                x_throw(sprintf('Failed to resolve value (%s)!', x_join(array_slice($keys, 0, $i + 1), '.')));
            }

            return $default;
        }
        $val = &$val[$key];
    }

    return $val;
}

//array set resolved path value
function x_array_set($path, &$arr, $val)
{
    if (empty($keys = x_split('.', $path, $count, $remove_empty=1))) {
        throw new Exception('Invalid array get path!');
    }
    if (!is_array($arr)) {
        $arr = [];
    }
    $tmp = &$arr;
    foreach ($keys as $i => $key) {
        if ($i == $count - 1) {
            $tmp[$key] = $val;

            continue;
        }
        if (!isset($tmp[$key])) {
            $tmp[$key] = [];
        } elseif (!is_array($tmp[$key])) {
            $tmp[$key] = x_to_array($tmp[$key]);
        }
        $tmp = &$tmp[$key];
    }

    return $arr;
}

//returns $val clone
function x_clone($val)
{
    if (is_object($val)) {
        return clone $val;
    }
    if (is_array($val)) {
        $buffer = [];
        foreach ($val as $key => $value) {
            $buffer[$key] = $value;
        }

        return $buffer;
    }

    return $val;
}

//insert $val into $arr (at $insert_at index)
function x_array_insert($val, array $arr, $insert_at=null, bool $insert_after=false)
{
    //convert val to array
    $varr = x_to_array($val);

    //if insert into array is invalid return converted array value/
    if (!is_array($arr)) {
        return $varr;
    }

    //get insert offset
    $offset = null;
    if (x_is_alphan($insert_at, 1)) {

        //get insert at key/index
        $insert_at = trim($insert_at);

        //set insert at offset
        if (($i = array_search($insert_at, array_keys($arr))) !== false && ($i = (int) $i) >= 0) {
            $offset = $insert_after ? $i + 1 : $i; //insert offset
        }
    }

    //insert if offset is integer
    if (is_integer($offset)) {
        $result = array_merge(
            array_slice($arr, 0, $offset),
            $varr,
            array_slice($arr, $offset)
        );
    }

    //append if offset is not an integer
    else {
        $result = array_merge($arr, $varr);
    }

    //result - modified array (update $arr)
    return $arr = $result;
}

//append to array if not exist
function x_list_add($val, array $arr, bool $unique=true)
{
    $arr = x_to_list($arr);
    if (!$unique || $unique && !in_array($val, $arr)) {
        $arr[] = $val;
    }

    return $arr;
}

//check if array has string keys
function x_has_list_keys(array $arr)
{
    if (!(is_array($arr) && !empty($arr))) {
        return false;
    }
    $count = count($keys = array_keys($arr));
    foreach ($keys as $key) {
        if (!(is_integer($key) && $key >= 0 && $key < $count)) {
            return false;
        }
    }

    return true;
}

//unset keys
function x_array_unset_keys(array $arr, ...$keys)
{
    if (!is_array($arr)) {
        return [];
    }
    array_map(function ($key) use (&$arr) {
        if (array_key_exists($key, $arr)) {
            unset($arr[$key]);
        }
    }, $keys);

    return $arr;
}

//unset values
function x_array_unset_values(array $arr, ...$values)
{
    if (!is_array($arr)) {
        return [];
    }
    array_map(function ($val) use (&$arr) {
        if (($i = array_search($val, $arr)) !== false) {
            unset($arr[$i]);
        }
    }, $values);

    return $arr;
}

//array only keys
function x_array_key_values(array $arr, string $key)
{
    if (!is_array($arr)) {
        return [];
    }
    if (x_is_assoc($arr)) {
        return array_key_exists($key, $arr) ? x_arr($arr[$key], 1) : [];
    }

    return array_column($arr, $key);
}

//array only keys
function x_array_only_keys(array $arr, $keys)
{
    if (!is_array($arr) || !count($keys = x_arr($keys, 1))) {
        return [];
    }
    $buffer = [];
    if ($is_assoc = x_is_assoc($arr)) {
        $arr = [$arr];
    }
    foreach ($arr as $item) {
        if (!is_array($item)) {
            continue;
        }
        $tmp = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $item)) {
                $tmp[$key] = $item[$key];
            }
        }
        if (!empty($tmp)) {
            $buffer[] = $tmp;
        }
    }
    if (!empty($buffer) && $is_assoc) {
        $buffer = $buffer[0];
    }

    return $buffer;
}
