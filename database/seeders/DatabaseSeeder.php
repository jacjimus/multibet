<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //seed settings
        $this->call(SettingsSeeder::class);

        //seed roles
        $this->call(RoleSeeder::class);

        //seed users
        $this->call(UserSeeder::class);
    }
}
