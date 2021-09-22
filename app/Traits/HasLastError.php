<?php

namespace App\Traits;

use Exception;

trait HasLastError
{
    //last error
    public $last_error;

    //returns $result (false) - sets $last_error (throws exception if $throwable is set)
    private function lastError($error, bool $throwable=false, $result=false)
    {
        //set last error
        $this->last_error = $error;

        //if $throwable throw exception
        if ($throwable) {
            throw new Exception($error);
        }

        //result
        return $result === 'error' ? $error : $result;
    }
}
