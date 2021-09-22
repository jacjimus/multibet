<?php

namespace App\Http\Controllers;

class NavController extends Controller
{
    //default
    public function __invoke()
    {
        return $this->welcome();
    }

    //welcome
    public function welcome()
    {
        return x_res_view('pages.welcome');
    }

    //logout
    public function logout()
    {
        app('App\Http\Controllers\Auth\LoginController')->logout();

        return redirect('/');
    }

    //login
    public function login()
    {
        return x_res_view('auth.login');
    }

    //verify
    public function verify()
    {
        return x_res_view('auth.verify');
    }
}
