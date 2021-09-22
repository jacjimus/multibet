<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;


class RulesProvider extends ServiceProvider
{
	/**
     * Bootstrap any application extended rules.
     *
     * @return void
     */
    public function boot(){
		Validator::extend('upload_file', 'App\Rules\Extend@upload_file');
		Validator::extend('region_code', 'App\Rules\Extend@region_code');
		Validator::extend('currency_code', 'App\Rules\Extend@currency_code');
		Validator::extend('phone_number', 'App\Rules\Extend@phone_number');
		Validator::extend('exists_or_null', 'App\Rules\Extend@exists_or_null');
    }
}
