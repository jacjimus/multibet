<?php

namespace App\Services;

use App\Models\Entry;

class SettingsService
{
    //get settings
    public function getSettings()
    {
        //settings buffer
        $settings = [];

        //buffer entries
        $entries = Entry::where('type', 'settings')->get();
        foreach ($entries as $entry) {
            $settings[$entry->key] = x_cast(
                $entry->data,
                $trim_string=1,
                $json_decode=1,
                $json_assoc=1,
                $decimal_places=2
            );
        }

        //result - settings
        return $settings;
    }
}
