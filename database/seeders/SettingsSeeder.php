<?php

namespace Database\Seeders;

use App\Models\Entry;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed the system settings.
     *
     * @return void
     */
    public function run()
    {
        //Settings Entries
        $entries = [
            //Footystats - UID
            [
                'type' => 'settings',
                'key' => 'footystats-uid',
                'data' => '50844',
            ],

            //Footystats - Username
            [
                'type' => 'settings',
                'key' => 'footystats-username',
                'data' => 'kukatinga',
            ],

            //Footystats - Password
            [
                'type' => 'settings',
                'key' => 'footystats-password',
                'data' => 'qaz123.5',
            ],

            //fs - predict win draw win
            [
                'type' => 'settings',
                'key' => 'fs-predict-wdw',
                'data' => '55',
            ],

            //fs - predict form
            [
                'type' => 'settings',
                'key' => 'fs-predict-form',
                'data' => '1.2',
            ],

            //fs - predict odds
            [
                'type' => 'settings',
                'key' => 'fs-predict-odds',
                'data' => '1.25',
            ],
        ];

        //entries - set entryable type
        $entryable_id = 0;
        $entryable_type = 'system';
        foreach ($entries as $key => $value) {
            $value['entryable_id'] = $entryable_id;
            $value['entryable_type'] = $entryable_type;
            $entries[$key] = $value;
        }

        //create entries
        foreach ($entries as $data) {
            //ignore existing
            if (Entry::where('type', $data['type'])->where('key', $data['key'])->exists()) {
                continue;
            }

            //create entry
            Entry::create($data);
        }
    }
}
