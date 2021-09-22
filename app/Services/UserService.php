<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;

class UserService
{
    //is authenticated
    public function isAuth()
    {
        return Auth::check();
    }

    //check if value is user model
    public function isUser($model)
    {
        return is_object($model) && $model instanceof User ? $model : false;
    }

    //get user (by id | authenticated)
    public function getUser(int $id=null, bool $throwable=false)
    {
        //get user by Id
        if (is_integer($id)) {
            if (!($user = User::find($id))) {
                if ($throwable) {
                    throw new Exception(sprintf('Failed to get user by Id (%s)', $id));
                }
            }

            return $user;
        }

        //get authenticated user
        if ($this->isAuth()) {
            if (!(($user = Auth::user()) instanceof User)) {
                if ($throwable) {
                    throw new Exception('Failed to get authenticated user!');
                }
            }

            return $user;
        }
    }

    //get root user (by id | first)
    public function getRootUser(int $id=null, bool $throwable=false)
    {
        //query user type = root
        $query = User::where('type', 'root');

        //get by id
        if (is_integer($id)) {
            $query->where('id', $id);
        }

        //result - query first
        if ($user = $query->first()) {
            return $user;
        }
        if ($throwable) {
            throw new Exception(sprintf('Failed to set system root user! (%s)', $id));
        }
    }

    //password hash
    public function passHash(string $str)
    {
        return bcrypt($str);
    }

    //create user
    public function createUser(array $data, bool $hash_password=false)
    {
        //check data
        if (!x_is_assoc($data)) {
            throw new Exception('Create user data is invalid.');
        }

        //default user data
        $default = [
            'type' => config('auth.user_type'),
            'status' => config('auth.user_status'),
        ];

        //set user data
        $data = array_replace_recursive($default, $data);

        //password - hash
        if ($hash_password && isset($data['password'])) {
            $data['password'] = $this->passHash($data['password']);
        }

        //email - lowercase
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        //username - lowercase
        if (isset($data['username'])) {
            $data['username'] = strtolower(trim($data['username']));
        }

        //name - ucwords
        if (isset($data['name'])) {
            $data['name'] = ucwords(trim($data['name']));
        }

        //create user
        $user = new User($data);
        $user->save();

        //attach user type role
        if ($role = Role::where('type', $user->type)->first()) {
            $user->roles()->attach($role->id);
        }

        //result - user
        return $user->fresh();
    }

    //get user premium
    public function getUserPremium($user_id)
    {
        //check cached values
        $cache_key = md5('user-premium-' . $user_id);
        if (x_cache_has($cache_key) && x_is_assoc($data = x_cache_get($cache_key))) {
            return $data; //return cached
        }

        //calculate user premiums
        $items = \App\Models\Payment::where('user_id', $user_id)
        -> where('provider', 'mpesa')
        -> orderBy('date', 'asc')
        -> get();

        // return ($items);
        $buffer = [];
        $end = 0;
        $days = 30000;
        $day = (60 * 60 * 24);
        $m = ($days * $day);
        foreach ($items as $item) {
            $date = $item->date;
            $dtime = strtotime($date);
            $bal = 0;
            if ($end && $dtime < $end) {
                $bal = floor(($end - $dtime)/$day);
                $end += $m;
            } else {
                $end = $dtime + $m;
            }
            $buffer[] = [
                'ref' => $item->ref,
                'date' => $date,
                'expiry' => Carbon::createFromTimestamp($end)->format('Y-m-d H:i:s'),
                'amount' => $item->amount,
                'phone' => $item->phone,
                'days' => $days,
                'bal' => $bal,
            ];
        }
        $now = now();
        $data = [
            'timestamp' => $now->format('Y-m-d H:i:s'),
            'expiry' => Carbon::createFromTimestamp($end)->format('Y-m-d H:i:s'),
            'items' => $buffer,
            'bal' => 0,
        ];
        $ntime = $now->getTimestamp();
        if ($end > $ntime) {
            $d = floor(($end - $ntime)/$day);
            $data['bal'] = $d;
        }

        //update cache data
        x_cache_set($cache_key, $data);

        //result
        return $data;
    }
}
