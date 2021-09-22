<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //validate password
        $rules = x_model_rules(new User, 0, 1);
        $this->validate($request, ['password' => $rules['password']]);

        //update
        $request->user()->update([
            'password' => app()->make('UserService')->passHash($request->password),
        ]);
    }
}
