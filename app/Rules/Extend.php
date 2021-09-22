<?php

namespace App\Rules;

use App\Traits\HasDatabase;
use Illuminate\Support\Facades\Validator;

class Extend
{
    //traits
    use HasDatabase;

    //upload_file:type (file|image|avatar|logo) - see config: services.upload
    public function upload_file($attribute, $value, $parameters, $validator)
    {
        $type = x_is_list($parameters, 0) ? $parameters[0] : null;
        $test = Validator::make(
            [$attribute => $value],
            [$attribute => new \App\Rules\UploadFile($type, $validator)]
        );
        if ($test->fails()) {
            $msg = $test->errors()->first($attribute);
            $validator->addReplacer('upload_file', function ($message, $attribute, $rule, $parameters) use (&$msg) {
                return $msg;
            });

            return false;
        }

        return true;
    }

    //region_code
    public function region_code($attribute, $value, $parameters, $validator)
    {
        $test = Validator::make(
            [$attribute => $value],
            [$attribute => new \App\Rules\RegionCode($validator)]
        );
        if ($test->fails()) {
            $msg = $test->errors()->first($attribute);
            $validator->addReplacer('region_code', function ($message, $attribute, $rule, $parameters) use (&$msg) {
                return $msg;
            });

            return false;
        }

        return true;
    }

    //currency_code
    public function currency_code($attribute, $value, $parameters, $validator)
    {
        $test = Validator::make(
            [$attribute => $value],
            [$attribute => new \App\Rules\CurrencyCode($validator)]
        );
        if ($test->fails()) {
            $msg = $test->errors()->first($attribute);
            $validator->addReplacer('currency_code', function ($message, $attribute, $rule, $parameters) use (&$msg) {
                return $msg;
            });

            return false;
        }

        return true;
    }

    //phone_number:region_code,region_attr
    public function phone_number($attribute, $value, $parameters, $validator)
    {
        $region_code = is_array($parameters) && isset($parameters[0]) ? trim($parameters[0]) : null;
        $region_attr = is_array($parameters) && isset($parameters[1]) ? trim($parameters[1]) : null;
        $region_code = !$region_code || strtoupper($region_code) == 'NULL' ? null : $region_code;
        $region_attr = !$region_attr || strtoupper($region_attr) == 'NULL' ? null : $region_attr;
        if (!$region_code && $region_attr) {
            $data = $validator->getData();
            if (isset($data[$region_attr])) {
                $region_code = trim($data[$region_attr]);
                $region_code = !$region_code || strtoupper($region_code) == 'NULL' ? null : $region_code;
            }
        }
        $test = Validator::make(
            [$attribute => $value],
            [$attribute => new \App\Rules\PhoneNumber($region_code, $region_attr, $validator)]
        );
        if ($test->fails()) {
            $msg = $test->errors()->first($attribute);
            $validator->addReplacer('phone_number', function ($message, $attribute, $rule, $parameters) use (&$msg) {
                return $msg;
            });

            return false;
        }

        return true;
    }

    //exists_or_null:table,field
    public function exists_or_null($attribute, $value, $parameters, $validator)
    {
        //set params - table, field
        if (!(is_array($parameters) && ($count_params = count($parameters)) >= 2)) {
            return false;
        }
        if (!strlen($table = trim($parameters[0]))) {
            throw new Exception('Rule exists_or_null parameters[0] "table" is missing.');

            return false;
        }
        if (!strlen($field = trim($parameters[1]))) {
            throw new Exception('Rule exists_or_null parameters[1] "field" is missing.');

            return false;
        }

        //normalize value
        if (($value = trim($value)) == '') {
            $value = null;
        }
        if ($value == '0' && strpos("$attribute $field", 'id') !== false) {
            $value = null;
        }

        //null is valid
        if (is_null($value)) {
            return true;
        }

        //validator replacer - :value $fields
        $field_values = "$field: $value";
        $validator->addReplacer('exists_or_null', function ($message, $attribute, $rule, $parameters) use (&$field_values) {
            if (strpos($message, ':value') !== false) {
                $message = str_replace(':value', $field_values, $message);
            }

            return $message;
        });

        //set db service
        $db = $this->getDB();

        //check if table field exists - throw exception on failure
        if (!$db->columnExists($table, $field, 1)) {
            return false;
        }

        //db query exists
        $query = $db->table($table)->where($field, $value);

        //query extra columns
        if ($count_params > 2) {
            //get validator data
            $data = $validator->getData();

            //set extra columns
            for ($i = 2; $i < $count_params; $i ++) {
                //ignore invalid
                if (!strlen($col = trim($parameters[$i]))) {
                    continue;
                }

                //check if column exists in data
                if (!array_key_exists($col, $data)) {
                    throw new Exception("Rule exists_or_null column '$col' was not found in validation data.");

                    return false;
                }

                //set column query
                $col_value = trim($data[$col]);
                $field_values .= ", $col: $col_value";
                $query->where($col, $col_value);
            }
        }

        //validate exists
        $exists = $query->exists();

        return $exists;
    }
}
