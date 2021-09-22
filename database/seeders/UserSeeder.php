<?php

namespace Database\Seeders;

use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the system users.
     *
     * @return void
     */
    public function run()
    {
        //get root user data config(auth.root_user)
        if (!x_is_assoc($root_user = config('auth.root_user'))) {
            throw new Exception('Invalid config "auth.root_user" data! Expected an associative array with keys (name, username, password).');
        }

        //system users
        $users = [
            //Root User
            [
                'type' => 'root',
                'name' => ucwords(trim($root_user['name'])),
                'username' => strtolower(trim($root_user['username'])),
                'password' => x_pass_hash($root_user['password']),
                'status' => 1, //active
            ],
        ];

        //create users
        $svc = app()->make('UserService');
        foreach ($users as $data) {
            //check if user exists - ignore existing
            $query = User::withTrashed()->where('id', '>=', 1);
            foreach (['username', 'email', 'phone_number'] as $key) {
                if (isset($data[$key]) && strlen($val = trim($data[$key]))) {
                    $query->where($key, $val);
                }
            }
            if ($query->exists()) {
                continue;
            }

            //create user
            $svc->createUser($data);
        }
    }
}
