<?php

namespace App\Traits;

trait HasZipService
{
    //vars
    protected $_zip_service;

    //get service
    protected function getZipService()
    {
        if (!$this->_zip_service) {
            $this->_zip_service = new \App\Services\ZipService;
        }

        return $this->_zip_service;
    }
}
