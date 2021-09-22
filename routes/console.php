<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

//command - inspire
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})
-> purpose('Display an inspiring quote');

//command - phpbin
Artisan::command('phpbin', function () {
    $this->info(PHP_BINARY);
})
-> purpose('Display php binary path.');
