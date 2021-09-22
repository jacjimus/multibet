<?php

namespace App\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Validator;

class PhoneNumber implements Rule
{
    /**
     * @var string phone number region code.
     */
    private $_region_code;

    /**
     * @var string region attribute.
     */
    private $_region_attr;

    /**
     * @var Validator
     */
    private $_validator;

    /**
     * @var string validation error.
     */
    private $_error;

    /**
     * Rule constructor.
     *
     * @param string    $region_code Phone region code.
     * @param string    $region_attr Validator data region code field name.
     * @param Validator $validator
     *
     * @return Rule
     */
    public function __construct(string $region_code=null, string $region_attr=null, Validator $validator=null)
    {
        $this->_region_code = $region_code;
        $this->_region_attr = $region_attr;
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
        //set validator
        $validator = ($validator = $this->_validator) instanceof Validator ? $validator : null;

        //validate
        if (x_is_phone_number($value, $this->_region_code, $valid_number, $error, $phone_data)) {
            if ($validator) {
                //get validator data
                $data = $validator->getData();

                //update phone attr
                $data[$attribute] = $valid_number;

                //update region attr
                if (strlen($region_attr = trim($this->_region_attr))) {
                    $data[$region_attr] = $phone_data['region'];
                }

                //update validator data
                $validator->setData($data);
            }

            //valid
            return true;
        }

        //set error
        $this->_error = $error instanceof Exception ? $error->getMessage() : $error;

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
        return trim(trans('validation.phone_number', ['error' => $this->_error]));
    }
}
