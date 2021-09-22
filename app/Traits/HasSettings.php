<?php

namespace App\Traits;

trait HasSettings
{
    //settings
    protected $_settings_service;

    protected $_settings;

    //get settings
    protected function getSettingsService()
    {
        if (!$this->_settings_service) {
            $this->_settings_service = app()->make('SettingsService');
        }

        return $this->_settings_service;
    }

    //fetch settings
    protected function getSettings(bool $refresh=false)
    {
        if (empty($this->_settings) || $refresh) {
            $this->_settings = $this->getSettingsService()->getSettings();
        }

        return $this->_settings;
    }
}
