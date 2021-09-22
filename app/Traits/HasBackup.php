<?php

namespace App\Traits;

trait HasBackup
{
    //vars
    protected $_backup_service;

    //get service
    protected function getBackupService()
    {
        if (!$this->_backup_service) {
            $this->_backup_service = app()->make('BackupService');
        }

        return $this->_backup_service;
    }
}
