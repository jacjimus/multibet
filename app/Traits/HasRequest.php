<?php

namespace App\Traits;

trait HasRequest
{
    //vars
    protected $_request_service;

    //get service
    protected function getRequestService()
    {
        if (!$this->_request_service) {
            $this->_request_service = app()->make('RequestService');
        }

        return $this->_request_service;
    }
}
