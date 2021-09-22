<?php

namespace App\Traits;

trait HasConsole
{
    //vars
    protected $_console_service;

    //get console service
    protected function getConsoleService()
    {
        if (!$this->_console_service) {
            $this->_console_service = app()->make('ConsoleService');
        }

        return $this->_console_service;
    }
}
