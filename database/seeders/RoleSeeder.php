<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the system roles.
     *
     * @return void
     */
    public function run()
    {
        //system roles
        $roles = [
            //Administrators
            [
                'type' => 'root',
                'name' => 'Administrators',
                'description' => 'Root system user group.',
                'status' => 1, //active
            ],

            //Basic Users
            [
                'type' => 'basic',
                'name' => 'Basic Users',
                'description' => 'Basic system user group.',
                'status' => 1, //active
            ],
        ];

        //create roles
        foreach ($roles as $data) {
            //ignore existing role (name)
            if (Role::where('name', $data['name'])->exists()) {
                continue;
            }

            //create role
            Role::create($data);
        }
    }
}
