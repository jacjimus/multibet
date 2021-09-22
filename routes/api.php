<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OauthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\ModelsController;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\NcmsController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;

//API Routes
Route::middleware('ncms-api')->group(function () {

    //contact form
    Route::post('contact-form', [NcmsController::class, 'contactForm']);

    //flutterwave callback
    Route::get('callback/flutterwave', [NcmsController::class, 'flutterwave']);

    //mpesa
    Route::post('stk-push', [MpesaController::class, 'stkPush']);
    Route::get('stk-poll/{trans_id}', [MpesaController::class, 'stkPoll']);
    Route::get('callback/m', [MpesaController::class, 'callback']);
    Route::post('callback/m', [MpesaController::class, 'callback']);
});

//API - Guest
Route::middleware('guest:api')->group(function () {

    //login
    Route::post('login', [LoginController::class, 'login']);

    //register
    Route::post('register', [RegisterController::class, 'register']);

    //password reset
    Route::post('password/reset', [ResetPasswordController::class, 'reset']);
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);

    //verification
    Route::post('email/resend', [VerificationController::class, 'resend']);
    Route::post('email/verify/{user}', [VerificationController::class, 'verify'])
    -> name('verification.verify');

    //oauth get url
    Route::post('oauth/{driver}', [OauthController::class, 'redirect']);

    //oauth callback (i.e. http://ncms.site/oauth/google/callback)
    Route::get('oauth/{driver}/callback', [OauthController::class, 'handleCallback'])
    -> name('oauth.callback');
});

//API - Auth
Route::middleware('auth:api')->group(function () {

    //user
    Route::get('user', [UserController::class, 'current']);

    //logout
    Route::post('logout', [LoginController::class, 'logout']);

    //settings
    Route::patch('settings/profile', [ProfileController::class, 'update']);
    Route::patch('settings/password', [PasswordController::class, 'update']);

    //NCMS Models
    Route::name('models.')->group(function () {
        Route::middleware('ncms-models')->group(function () {

            //models.show
            Route::get('get/{path}', [ModelsController::class, 'show'])
            -> where('path', '(.*)')
            -> name('show');

            //models.create
            Route::post('create/{path}', [ModelsController::class, 'create'])
            -> where('path', '(.*)')
            -> name('create');

            //models.update - post
            Route::post('update/{path}', [ModelsController::class, 'update'])
            -> where('path', '(.*)')
            -> name('update');

            //models.patch (update)
            Route::patch('update/{path}', [ModelsController::class, 'update'])
            -> where('path', '(.*)')
            -> name('patch');

            //models.delete
            Route::post('delete/{path}', [ModelsController::class, 'delete'])
            -> where('path', '(.*)')
            -> name('delete');

            //models.restore
            Route::post('restore/{path}', [ModelsController::class, 'restore'])
            -> where('path', '(.*)')
            -> name('restore');

            //models.destroy
            Route::post('destroy/{path}', [ModelsController::class, 'destroy'])
            -> where('path', '(.*)')
            -> name('destroy');
        });
    });
});
