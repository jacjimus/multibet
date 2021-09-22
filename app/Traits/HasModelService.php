<?php

namespace App\Traits;

trait HasModelService
{
    //vars
    protected $_model_service;

    //get console service
    protected function getModelService()
    {
        if (!$this->_model_service) {
            $this->_model_service = app()->make('ModelService');
        }

        return $this->_model_service;
    }
}
