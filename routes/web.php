<?php

use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\NcmsController;
use Illuminate\Support\Facades\Route;

//Main Routes
Route::middleware([
    'ncms-web',
    'ncms-minify',
    'ncms-signature',
    'fs-premium',
])
-> group(function () {

    //Welcome
    Route::view('/', 'fstats.welcome.index')
    -> middleware('fs-matches')
    -> name('welcome');

    //Pages
    Route::view('/disclaimer', 'pages.disclaimer');
    Route::view('/privacy-policy', 'pages.privacy-policy');
    Route::view('/terms-of-use', 'pages.terms-of-use');

    //Guest Pages
    Route::middleware(['guest'])->group(function () {
        Route::view('/login', 'auth.login');
        Route::view('/register', 'auth.register');
        Route::view('/email/verify/{user_id}', 'auth.verify')->name('verify');
        Route::view('/password-recovery', 'auth.password-recovery');
        Route::get('/password-reset/{token}/{email}', [ResetPasswordController::class, 'passwordReset']);
    });

    Route::view('/home', 'pages.home');

    //Auth Pages
    Route::middleware(['auth'])->group(function () {
        Route::get('/logout', [NcmsController::class, 'logout']);
        Route::view('/premium', 'pages.premium');
        Route::view('/profile', 'profile.page');
        Route::view('/settings', 'settings.page');
    });
});

//System Routes (NCMS)
Route::name('ncms.')->group(function () {

    //Images
    Route::get('/favicon.ico', [NcmsController::class, 'favicon']);
    Route::get('/image.png', [NcmsController::class, 'image']);
    Route::get('/logo.png', [NcmsController::class, 'logo']);
    Route::get('/icon.png', [NcmsController::class, 'icon']);

    //Developer
    Route::middleware('ncms-dev')->group(function () {
        Route::get('x-console/{cmd?}/{type?}', [NcmsController::class, 'console']);
        Route::get('x-view/{path}', [NcmsController::class, 'showView']);
        Route::get('x-link', [NcmsController::class, 'link']);
        Route::get('x-info', [NcmsController::class, 'info']);

        Route::get('x-test', [NcmsController::class, 'test']);
        Route::post('x-test', [NcmsController::class, 'test']);
    });
});
