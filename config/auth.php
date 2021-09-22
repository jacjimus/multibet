<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => 10800,

    /*
    |--------------------------------------------------------------------------
    | NCMS | User password rules
    |--------------------------------------------------------------------------
    |
    | Here you may define the users model password field edit rules.
    |
    */

    'password_rules' => ['min:6'],

	/*
    |--------------------------------------------------------------------------
    | NCMS | Email Verification
    |--------------------------------------------------------------------------
    |
    | Here you can set whether to enforce email verification for new logins.
    |
    */
	
	'verify_email' => (bool) env('AUTH_VERIFY_EMAIL', true),

    /*
    |--------------------------------------------------------------------------
    | NCMS | Default User Type
    |--------------------------------------------------------------------------
    |
    | Here you may define the default type value for new users.
    |
    | The user type values include:
    | root = Root user (system administrator)
    | basic = Basic user (system user - access mainly determined by user role)
    |
    */

    'user_type' => 'basic',

    /*
    |--------------------------------------------------------------------------
    | NCMS | Default User Status
    |--------------------------------------------------------------------------
    |
    | Here you may define the default status value for new users and roles.
    |
    | The status values include:
    | 0 = Disabled
    | 1 = Active
    |
    */

    'user_status' => 1,
    'role_status' => 1,

    /*
    |--------------------------------------------------------------------------
    | NCMS | Root User Defaults
    |--------------------------------------------------------------------------
    |
    | Here you may define the root user default values. The root user is created
    | after creating the users table during database migration. The default values
    | correspond to the fields in the users table.
    |
    | Note that the password value will be hashed during creation so only configure
    | plain text password to avoid hashing hash.
    |
    */

    'root_user' => [
        'name' => env('ROOT_NAME', 'Administrator'),
        'username' => env('ROOT_USERNAME', 'root'),
        'password' => env('ROOT_PASSWORD', 'Admin@123'),
    ],

	/*
    |--------------------------------------------------------------------------
    | NCMS | OAuth Providers
    |--------------------------------------------------------------------------
    |
    | Here you may define the supported oauth service providers (comma delimited).
    |
    */

	'oauth_providers' => env('AUTH_OAUTH_PROVIDERS', 'google,facebook,twitter'),

];
