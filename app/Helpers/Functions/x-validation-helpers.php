<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator as Validation;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

//validation
function x_validate(array $input, array $rules, array $messages=null, &$valid=null, &$validator=null)
{
    //clear valid, validator
    $valid = null;
    $validator = null;

    //check input, rules, messages
    if (!x_is_assoc($input)) {
        throw new Exception('XValidate $input is not a valid assoc array.');
    }
    if (!x_is_assoc($rules)) {
        throw new Exception('XValidate $rules is not a valid assoc array.');
    }
    if ($messages != null && !x_is_assoc($messages)) {
        throw new Exception('XValidate $messages is not a valid assoc array.');
    }

    //validator
    $validator = Validation::make($input, $rules, x_arr($messages));

    //invalid
    if ($validator->fails()) {
        return false;
    }

    //valid
    $valid = $validator->valid();

    return true;
}

//throw validation errors
function x_throw_validation($error)
{
    if ($error instanceof Validator) {
        throw new ValidationException($error);
    }
    if (is_object($error) && method_exists($error, 'toArray')) {
        $error = $error->toArray();
    }
    if (!x_is_assoc($error = x_arr($error, 1))) {
        $error = ['error' => $error];
    }

    throw ValidationException::withMessages($error);
}

//is region code
function x_is_region_code($val, &$valid_code=null, &$code_data=null)
{
    static $codes;
    $code_data = null;
    $valid_code = null;
    if (!isset($codes)) {
        $codes = config('region_codes');
    }
    if (strlen($code = strtoupper(trim($val))) > 1 && array_key_exists($code, $codes)) {
        $valid_code = $code;
        $code_data = $codes[$code];

        return true;
    }

    return false;
}

//is currency code
function x_is_currency_code($val, &$valid_code=null, &$code_data=null)
{
    static $codes;
    $code_data = null;
    $valid_code = null;
    if (!isset($codes)) {
        $codes = config('currency_codes');
    }
    if (strlen($code = strtoupper(trim($val))) > 1 && array_key_exists($code, $codes)) {
        $valid_code = $code;
        $code_data = $codes[$code];

        return true;
    }

    return false;
}

//is phone number
function x_is_phone_number($val, $region_code=null, &$valid_number=null, &$error=null, &$phone_data=null)
{
    static $util;

    //byref defaults
    $valid_number = null;
    $phone_data = null;
    $error = null;

    //check phone number
    if (strlen($phone_number = trim($val)) < 3) {
        return false;
    }

    //normalize phone number
    $has_code = $phone_number[0] == '+';
    if (strlen($phone_number = trim(preg_replace('/[^0-9]/', '', $phone_number))) < 3) {
        return false;
    }
    $phone_number = ($has_code ? '+' : '') . $phone_number;

    //check phone region code
    $region_code = x_is_region_code($region_code, $tmp) ? $tmp : null;

    //set PhoneNumberUtil
    if (!isset($util)) {
        $util = PhoneNumberUtil::getInstance();
    }

    try {
        //parse phone number
        $region_default = config('app.region');
        $phone = $util->parse($phone_number, $region_code ? $region_code : $region_default);

        //check invalid
        if (!$util->isValidNumber($phone)) {
            return false;
        } //throw new Exception('Invalid phone number.', 1);

        //set phone data
        $data = [
            'phone_number' => $phone_number,
            'region_code' => $region_code,
            'region_default' => $region_default,
            'international' => $util->format($phone, PhoneNumberFormat::E164),
            'region' => $util->getRegionCodeForNumber($phone),
            'code' => $phone->getCountryCode(),
        ];

        //check region code match
        if ($region_code && !$util->isValidNumberForRegion($phone, $region_code)) {
            throw new Exception(sprintf('The phone region code "%s" does not match actual phone region code "%s".', $region_code, $data['region']), 2);
        }

        //valid - set data
        $valid_number = $data['international'];
        $phone_data = $data;

        return true;
    } catch (Exception $e) {
        $error = $e;
    } catch (NumberParseException $e) {
        $error = $e;
    }

    //invalid
    return false;
}

//model validation rules
function x_model_rules($class, $id=null, bool $password_required=false)
{
    if (is_object($class)) {
        $class = get_class($class);
    }
    $model = is_string($class) && ($class = trim($class)) ? app()->make($class) : null;
    if (!($model instanceof Model)) {
        throw new Exception("Invalid rules model class ($class)!");
    }
    if (empty($rules = $model->getRules())) {
        return [];
    }
    if ($password_required && isset($rules['password'])) {
        $rules['password'] = array_unique(array_merge(['required', 'confirmed'], x_array_unset_values($rules['password'], 'nullable')));
    }
    if (($id = x_int($id, 1, 0)) > 1) {
        $__replace = function ($val) use (&$__replace, &$id) {
            if (is_array($val)) {
                $tmp = [];
                foreach ($val as $key => $value) {
                    $tmp[$key] = $__replace($value);
                }

                return $tmp;
            }
            if (is_string($val)) {
                $pattern = '/unique\\:([^,]+),([^,]+),NULL,NULL/i';
                if (preg_match($pattern, $val)) {
                    $val = preg_replace($pattern, 'unique:$1,$2,' . $id, $val);
                }
            }

            return $val;
        };
        $rules = $__replace($rules);
    }
    foreach ($rules as $key => $val) {
        if (x_is_list($val, 0)) {
            $tmp = -1;
            foreach ($val as $i => $rule) {
                if ($rule == 'string') {
                    $tmp = $i;
                }
                if (strpos($rule, 'upload_file') !== false) {
                    if ($tmp >= 0) {
                        unset($val[$tmp]);
                        $rules[$key] = $val;
                    }
                }
            }
        }
    }

    return $rules;
}

//validate model
function x_validate_model($model, array $messages=null, $parent=null)
{
    if (!($model instanceof Model)) {
        throw new Exception('Invalid model validation $model.');
    }
    if (!is_null($parent) && !($model instanceof Model)) {
        throw new Exception('Invalid model validation $parent.');
    }
    //dd($model->getInput(1));
    if (!x_is_assoc($input = $model->getInput(1))) {
        // throw new Exception('Undefined model validation input.');
    }
    $rules = x_is_assoc($rules = x_model_rules($model, $model->getId())) ? $rules : [];
    $model_uid = $model->getUid();

    //set validation input
    $model_input = [];
    $relations_input = [];
    $fields = $model->getFields();
    $relations = $model->getRelationships();
    foreach ($input as $key => $val) {
        if (in_array($key, $fields)) {
            $model_input[$key] = $val;
        } elseif (in_array($key, $relations)) {
            $relation_rules = null;
            if (array_key_exists($key, $rules)) {
                $relation_rules = $rules[$key];
                unset($rules[$key]);
            }
            $relations_input[$key] = [$val, $relation_rules];
        }
    }

    //validate model input
    if (x_is_assoc($rules) && x_is_assoc($model_input)) {
        //dump($model_input);
        if (!x_validate($model_input, $rules, null, $valid, $validator)) {
            if ($validator) {
                x_throw_validation($validator);
            }

            return false;
        }
        if (x_is_assoc($valid)) {
            foreach ($valid as $key => $value) {
                $model_input[$key] = $value;
            }
            $model->setInput($model_input);
            $model->fill($model_input);
        }
    }

    //valid
    return true;

    /*
    //validate relations input
    if (x_is_assoc($relations_input)){
        foreach($relations_input as $key => $value){
            $val = $value[1];
            $val_rules = $value[2];
            $relation = $model->{$key}();
            $related_class = get_class($relation->getRelated());
            $related_model = app($related_class);
            if ($related_model->getOptions('upload')){
                //if ($related_model)
                //..
            }
            //$related_class
            //$value
            //..
        }
    }
    */
}

//eof
