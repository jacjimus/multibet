<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Validator;

class UploadFile implements Rule
{
    /**
     * @var string file type (see services.upload).
     */
    private $_type;

    /**
     * @var Validator
     */
    private $_validator;

    /**
     * @var string internal errors.
     */
    private $_errors = [];

    /**
     * Rule constructor.
     *
     * @param Validator $validator
     *
     * @return Rule
     */
    public function __construct(string $type='file', Validator $validator=null)
    {
        $this->_type = $type;
        $this->_validator = $validator;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //dd($value);
        //upload service save file
        $s = app('UploadService');
        $file = $s->save($value, $this->_type, $attribute, $str_ignore=1, $name, $errors);
        if ($file) {

            //update validator data attribute
            if (($validator = $this->_validator) instanceof Validator) {
                $data = $validator->getData();
                $data[$attribute] = $file;
                $data[$attribute . '_name'] = $name;
                $validator->setData($data);
            }

            //valid
            return true;
        } elseif ($file === null) {
            return true;
        }

        //set errors
        $this->_errors = $errors;

        //fail
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if (x_is_list($errors = $this->_errors, 1)) {
            return implode("\r\n", $errors);
        }

        return trans('validation.file');
    }
}
