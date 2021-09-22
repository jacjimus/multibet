<?php

namespace App\Traits;

trait HasDatabase
{
    //vars
    protected $_database_service;

    //get service
    protected function getDatabaseService()
    {
        if (!$this->_database_service) {
            $this->_database_service = new \App\Services\DatabaseService;
        }

        return $this->_database_service;
    }

    //get service
    protected function getDB()
    {
        return $this->getDatabaseService();
    }
}
