<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Validator;

class CurrencyCode implements Rule
{
    /**
     * @var Validator
     */
    private $_validator;

    /**
     * Rule constructor.
     *
     * @param Validator $validator
     *
     * @return Rule
     */
    public function __construct(Validator $validator=null)
    {
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
        //validation
        if (x_is_currency_code($value, $code)) {
            //update validator data attribute
            if (($validator = $this->_validator) instanceof Validator) {
                $data = $validator->getData();
                $data[$attribute] = $code;
                $validator->setData($data);
            }

            //valid
            return true;
        }

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
        return trans('validation.currency_code');
    }
}
