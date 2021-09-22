<?php

return [
	/*
    |--------------------------------------------------------------------------
    |   App PHP Binary Path
    |--------------------------------------------------------------------------
    |
    |   You can check out your current php binary path by running.
    |	$ php artisan phpbin
    |	This is the binary used in console service php commands.
    |
    */
    
    'phpbin' => env('APP_PHPBIN', '/usr/local/bin/php'),
    
	/*
    |--------------------------------------------------------------------------
    |   App Build Number
    |--------------------------------------------------------------------------
    |
    |   This value is the application's build number.
    |	This value is used in assets url to enforce build updates.
    |
    */

    'build' => env('APP_BUILD', time()),
    
	/*
    |--------------------------------------------------------------------------
    |   Public Path
    |--------------------------------------------------------------------------
    |
    |   This value is the application's public path. This value is used as the
	|	public path if $_SERVER['DOCUMENT_ROOT'] is undefined.
	|	The default value is base_path('public')
    |
    */

    'public' => env('APP_PUBLIC'),

	/*
    |--------------------------------------------------------------------------
    |   Application Package
    |--------------------------------------------------------------------------
    |
    |   This value is name of your aplication package. The value is used in to
	|   specify package resources.
	|	i.e. By default, the application uses asset('assets/images/icon.png')
	|	as the preferred icon. If asset('assets/images/[APP_PACKAGE]/icon.png')
	|	is available, it will be used instead.
    |
    */

    'package' => env('APP_PACKAGE'),

	/*
    |--------------------------------------------------------------------------
    |   Application Name
    |--------------------------------------------------------------------------
    |
    |   This value is the name of your application. This value is used when the
    |   framework needs to place the application's name in a notification or
    |   any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'NCMS'),

    /*
    |--------------------------------------------------------------------------
    |   Application Owner
    |--------------------------------------------------------------------------
    |
    |   This value is the name of your application owner.
    |   Mainly used in the page view meta tags among other uses.
    |
    */

    'owner' => env('APP_OWNER'),

    /*
    |--------------------------------------------------------------------------
    |   Application Description
    |--------------------------------------------------------------------------
    |
    |   This value is the application description.
    |   Mainly used in the page view meta tags among other uses.
    |
    */

    'description' => env('APP_DESCRIPTION', 'A Laravel content manager.'),
    
    /*
    |--------------------------------------------------------------------------
    |   Application Keywords (Tags)
    |--------------------------------------------------------------------------
    |
    |   This value is the application keywords.
    |   Mainly used in the page view meta tags among other uses.
    |
    */

    'keywords' => env('APP_KEYWORDS', 'laravel, content, manager, ncms, cms, server'),
    
    /*
    |--------------------------------------------------------------------------
    |   Application Image (Meta)
    |--------------------------------------------------------------------------
    |
    |   This value is the application "image" image asset path.
    |   Mainly used in the page view meta tags among other uses.
    |
    */

    'image' => env('APP_IMAGE', 'assets/images/app.png'),

    /*
    |--------------------------------------------------------------------------
    |   Application Icon
    |--------------------------------------------------------------------------
    |
    |   This value is the application "icon" image asset path.
    |   Mainly used in the page view meta tags among other uses.
	|	This file is also used as the /favicon.ico
    |
    */

    'icon' => env('APP_ICON', 'assets/images/icon.png'),

    /*
    |--------------------------------------------------------------------------
    |   Application Logo
    |--------------------------------------------------------------------------
    |
    |   This value is the application "logo" image asset path.
    |
    */

    'logo' => env('APP_LOGO', 'assets/images/logo.png'),

    /*
    |--------------------------------------------------------------------------
    |   Application Environment
    |--------------------------------------------------------------------------
    |
    |   This value determines the "environment" your application is currently
    |   running in. This may determine how you prefer to configure various
    |   services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    |   Application Debug Mode
    |--------------------------------------------------------------------------
    |
    |   When your application is in debug mode, detailed error messages with
    |   stack traces will be shown on every error that occurs within your
    |   application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    |   Application URL
    |--------------------------------------------------------------------------
    |
    |   This URL is used by the console to properly generate URLs when using
    |   the Artisan command line tool. You should set this to the root of
    |   your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://ncms.site'),

    /*
    |--------------------------------------------------------------------------
    |   Application Timezone
    |--------------------------------------------------------------------------
    |
    |   Here you may specify the default timezone for your application, which
    |   will be used by the PHP date and date-time functions. We have gone
    |   ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Africa/Nairobi',

	/*
    |--------------------------------------------------------------------------
    |   Application Region Code
    |--------------------------------------------------------------------------
    |
    |   Here you may specify the default region code for your application, which
    |   will be used as the default region code where needed.
	|	For list of region codes: array_keys(config('region_codes'))
    |
    */

	'region' => env('APP_REGION', 'KE'),

	/*
    |--------------------------------------------------------------------------
    |   Application Currency Code
    |--------------------------------------------------------------------------
    |
    |   Here you may specify the default currency code for your application,
	|	which will be used as the default currency code where needed.
	|	For list of currency codes: array_keys(config('currency_codes'))
    |
    */

	'currency' => env('APP_CURRENCY', 'KES'),

    /*
    |--------------------------------------------------------------------------
    |   Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    |   The application locale determines the default locale that will be used
    |   by the translation service provider. You are free to set this value
    |   to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    'locales' => [
        'en' => 'EN',
        /*
        'es' => 'ES',
        'fr' => 'FR',
        'pt-BR' => 'BR',
        'zh-CN' => '中文',
        */
    ],

    /*
    |--------------------------------------------------------------------------
    |   Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    |   The fallback locale determines the locale to use when the current one
    |   is not available. You may change the value to correspond to any of
    |   the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    |   Faker Locale
    |--------------------------------------------------------------------------
    |
    |   This locale will be used by the Faker PHP library when generating fake
    |   data for your database seeds. For example, this will be used to get
    |   localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    |   Encryption Key
    |--------------------------------------------------------------------------
    |
    |   This key is used by the Illuminate encrypter service and should be set
    |   to a random, 32 character string, otherwise these encrypted strings
    |   will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    |   Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    |   The service providers listed here will be automatically loaded on the
    |   request to your application. Feel free to add your own services to
    |   this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        Intervention\Image\ImageServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        #App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\ConfigSetupProvider::class,
        App\Providers\HelpersProvider::class,
        App\Providers\ServicesProvider::class,
        App\Providers\RulesProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    |   Class Aliases
    |--------------------------------------------------------------------------
    |
    |   This array of class aliases will be registered when this application
    |   is started. However, feel free to register as many as you wish as
    |   the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Http' => Illuminate\Support\Facades\Http::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
        'Image' => Intervention\Image\Facades\Image::class,
    ],
];
