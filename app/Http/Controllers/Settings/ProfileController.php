<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Update the user's profile information.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //set user - update keys
        $user = $request->user();
        $keys = ['name', 'email'];

        //validate
        $rules = x_array_only_keys(x_model_rules(new User, $user->id), $keys);
        $this->validate($request, $rules);

        //update
        return tap($user)->update($request->only(...$keys));
    }
}
