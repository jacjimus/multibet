<?php

//get date string format
function x_get_date_format(string $date, $default=null, &$date_string=null, &$date_string_format=null)
{
    //modified date string
    $date_string = null;
    $date_string_format = null;

    //time fix
    $time_h = '';
    $time_sep = '';
    $time_del = '';
    if (preg_match('/(^|[^\d\-\/])([\d\:]+)$/', trim($date), $matches)) {
        //set vars
        $time_sep = $matches[1];
        $time = $matches[2];

        //set time delimiter
        if (strpos($time, ':') !== false) {
            $time_del=':';
        }

        //set time parts
        $parts = array_map(function ($str) {
            return str_pad($str, 2, '0', STR_PAD_LEFT);
        }, str_split(str_replace($time_del, '', $time), 2));

        //set time string
        if (count($parts) == 1) {
            $time_h = $parts[0];
            $time = 'H';
        } else {
            $time = implode(':', $parts);
        }

        //replace fixed time
        $date = str_replace($matches[0], " $time", $date);
    }

    //check Day->(0[1-9]|[1-2][0-9]|3[0-1])
    //check Month->(0[1-9]|1[0-2])
    //check Year->[0-9]{4} or \d{4}
    $patterns = [
        '/\b\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{3,8}Z\b/' => 'Y-m-d\TH:i:s.u\Z', // format DATE ISO 8601
        '/\b\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\b/' => 'Y-m-d',
        '/\b\d{4}-(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])\b/' => 'Y-d-m',
        '/\b(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-\d{4}\b/' => 'd-m-Y',
        '/\b(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])-\d{4}\b/' => 'm-d-Y',

        '/\b\d{4}\/(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\b/' => 'Y/d/m',
        '/\b\d{4}\/(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\b/' => 'Y/m/d',
        '/\b(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/\d{4}\b/' => 'd/m/Y',
        '/\b(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\/\d{4}\b/' => 'm/d/Y',

        '/\b\d{4}\.(0[1-9]|1[0-2])\.(0[1-9]|[1-2][0-9]|3[0-1])\b/' => 'Y.m.d',
        '/\b\d{4}\.(0[1-9]|[1-2][0-9]|3[0-1])\.(0[1-9]|1[0-2])\b/' => 'Y.d.m',
        '/\b(0[1-9]|[1-2][0-9]|3[0-1])\.(0[1-9]|1[0-2])\.\d{4}\b/' => 'd.m.Y',
        '/\b(0[1-9]|1[0-2])\.(0[1-9]|[1-2][0-9]|3[0-1])\.\d{4}\b/' => 'm.d.Y',

        // for 24-hour | hours seconds
        '/\b(?:2[0-3]|[01][0-9]):[0-5][0-9](:[0-5][0-9])\.\d{3,6}\b/' => 'H:i:s.u',
        '/\b(?:2[0-3]|[01][0-9]):[0-5][0-9](:[0-5][0-9])\b/' => 'H:i:s',
        '/\b(?:2[0-3]|[01][0-9]):[0-5][0-9]\b/' => 'H:i',

        // for 12-hour | hours seconds
        '/\b(?:1[012]|0[0-9]):[0-5][0-9](:[0-5][0-9])\.\d{3,6}\b/' => 'h:i:s.u',
        '/\b(?:1[012]|0[0-9]):[0-5][0-9](:[0-5][0-9])\b/' => 'h:i:s',
        '/\b(?:1[012]|0[0-9]):[0-5][0-9]\b/' => 'h:i',

        '/\.\d{3}\b/' => '.v'
    ];

    //set format (patterns replace $date)
    $format = preg_replace(array_keys($patterns), array_values($patterns), $date);

    //validate format
    if (!($date == $format || preg_match('/\d/', $format))) {
        //set date string
        $date_string = $time_h ? str_replace('H', $time_h, $date) : $date;
        $date_string_format = $format;

        //set format time fixes
        if (preg_match('/\s?(h(\:i)?(\:s)?)$/i', $format, $matches)) {
            //set time format
            $time_format = $time_sep . str_replace(':', $time_del, $matches[1]);

            //replace time format
            $format = str_replace($matches[0], $time_format, $format);
        }
    }

    //set default
    else {
        $format = $default;
    }

    //result - format
    return $format;
}

//parse date string
function x_date_parse(string $date, string $format='Y-m-d H:i:s', $default=null, bool $strict=false)
{
    //attempt to parse using $format
    $dt = DateTime::createFromFormat($format, $date);

    //try parse corrections of not strict
    if ($dt === false && !$strict) {
        //try parsing using detected format
        if ($date_format = x_get_date_format($date, null, $date_string, $date_string_format)) {
            $dt = DateTime::createFromFormat($date_string_format, $date_string);
        }

        //try creating
        if (!($dt instanceof DateTime)) {
            try {
                $dt = new DateTime($date);
            } catch (Exception $e) {
                $dt = null;
            }
        }
    }

    //validate - set default
    if (!($dt instanceof DateTime)) {
        //if default is true use current timestamp
        if ($default === true) {
            $default = new DateTime();
        }

        //set default
        $dt = $default;
    }

    //result - DateTime/$default
    return $dt;
}

//date format
function x_date_format(...$args)
{
    x_udate(...$args);
}

//get timestamp
function x_utime(
    $val=null,
    bool $to_day_start=false,
    string $parse_format='Y-m-d H:i:s',
    $default=true,
    &$dt=null
) {
    //set datetime
    $dt = null;
    if ($val instanceof DateTime) {
        $dt = $val;
    }

    //from timestamp
    if (is_numeric($val) && x_is_int($val, 1, $num)) {
        if (strlen($num) == 13) {
            $num = (int) ($num/1000);
        }
        $dt = new DateTime();
        $dt->setTimestamp($num);
    }

    //from datetime string
    if (is_null($dt) && x_is_alphan($val, 1)) {
        $dt = x_date_parse($val, $parse_format, null, $strict=0);
    }

    //set default
    if (is_null($dt)) {
        //if default is true use current timestamp
        if ($default === true) {
            $dt = new DateTime();
        }

        //set default timestamp
        elseif (x_is_alphan($default, 1)) {
            x_utime($default, $to_day_start, $parse_format, null, $dt);
        }
    }

    //validate datetime - return null if invalid
    if (!($dt instanceof DateTime)) {
        return null;
    }

    //to day start (midnight)
    if ($to_day_start) {
        $dt->setTime(0, 0, 0);
    }

    //result - timestamp
    return $dt->getTimestamp();
}

//get datetime|formatted string
function x_udate(
    $val,
    string $format='Y-m-d H:i:s',
    string $parse_format='Y-m-d H:i:s',
    bool $to_day_start=false,
    $default=null,
    &$dt=null
) {
    //set datetime
    $dt = null;
    if ($val instanceof DateTime) {
        $dt = $val;
    }

    //parse value - set $datetime
    if (is_null($dt) && x_is_alphan($val, 1)) {
        x_utime($val, $to_day_start, $parse_format, null, $dt);
    }

    //set default
    if (!($dt instanceof DateTime)) {
        //if default is true use current timestamp
        if ($default === true) {
            $dt = new DateTime();
        }

        //set default timestamp
        elseif (x_is_alphan($default, 1)) {
            x_utime($default, $to_day_start, $parse_format, null, $dt);
        }
    }

    //validate datetime - return null if invalid
    if (!($dt instanceof DateTime)) {
        return null;
    }

    //to day start (midnight)
    if ($to_day_start) {
        $dt->setTime(0, 0, 0);
    }

    //result - datetime/format
    return x_is_string($format, 1) ? $dt->format($format) : $dt;
}
