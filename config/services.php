<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */
	
	//mailgun
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],
	
	//postmark
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
	
	//ses
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
	
	//google
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
		'analytics_id' => env('GOOGLE_ANALYTICS_ID'),
    ],
    
    //twitter
    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
    ],
    
    //facebook
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    ],
    
    //flutterwave
    'flutterwave' => [
		'mode' => env('FLUTTERWAVE_MODE', 'test'),
		'public' => env('FLUTTERWAVE_PUBLIC'),
		'secret' => env('FLUTTERWAVE_SECRET'),
		'enc' => env('FLUTTERWAVE_ENC'),
		'test' => [
			'public' => env('FLUTTERWAVE_TEST_PUBLIC'),
			'secret' => env('FLUTTERWAVE_TEST_SECRET'),
			'enc' => env('FLUTTERWAVE_TEST_ENC'),
		],
    ],
    
    //mpesa
    'mpesa' => [
		'mode' => 'live', //live|sandbox
		'log_callback' => true,
		'stk_amount' => 299,
		'shortcode' => 554441,
		'stk_push' => 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
		'stk_query' => 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query',
		'register_url' => 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl',
		'oauth' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
		'passkey' => '74bee020449e93331e9c2c99c3b883232b235257c9f6a6f2b1aa0d7903a47788',
		'consumer_key' => '3SicTbADcoa4mqYONdURdvWxWHDtEXHM',
		'consumer_secret' => 'OYOhrvAGLZ7dgsaR',
		'confirmation_url' => 'https://bingpredict.com/api/callback/m',
		'validation_url' => 'https://bingpredict.com/api/callback/m',
		'sandbox' => [
			'shortcode' => 174379,
			'stk_push' => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
			'stk_query' => 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query',
			'regiter_url' => 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl',
			'oauth' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
			'passkey' => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
			'consumer_key' => 'xtZVg3t40oG7vJdHsZwKJR2WTOxDYeYM',
			'consumer_secret' => '0XKQBmnzMZUggJfC',
			'confirmation_url' => 'https://9542ef6ca9f3.ngrok.io/api/callback/m/?type=confirmation',
			'validation_url' => 'https://9542ef6ca9f3.ngrok.io/api/callback/m/?type=validation',
		],
	],
    
    /*
    |--------------------------------------------------------------------------
    | NCMS | Upload Service
    |--------------------------------------------------------------------------
    | \App\Services\UploadService::class
    | 
    | Here you may configure the default options for file upload service.
    | You can add custom file_types following the examples below to support more
    | file mime types.
    |
    */
    
    //upload
    'upload' => [
    
		//uploads folder (relative to storage_path('app/public'))
		'dir' => 'uploads',
		
		//path template - i.e. table=users,field=avatar > users/avatar/[UUID].png
		'path_template' => ':model_table/:id/:model_field/:uuid.:ext',
		
		//file types
		'file_types' => [
			
			//file
			'file' => [
				'url' => true,					//allow url uploads
				'max' => (1024 * 5),			//upload max size (5120 kb (5mb))
				'mimes' => [					//supported upload mime types (['mime-type' => 'ext'])
					'image/jpeg' => 'jpeg',
					'image/jpg' => 'jpg',
					'image/png' => 'png',
					'image/gif' => 'gif',
					'application/pdf' => 'pdf',
					'application/zip' => 'zip',
				],
			],
			
			//image
			'image' => [
				'extend' => 'file',				//extend file type config (array_merge)
				'mimes' => [
					'image/jpeg' => 'jpeg',
					'image/jpg' => 'jpg',
					'image/png' => 'png',
					'image/gif' => 'gif',
				],
				'dimensions_min' => 32,		//number|[min_width, min_height] (if number, same value is used for width & height)
				'dimensions_max' => 1024,		//number|[max_width, max_height] (if number, same value is used for width & height)
				'resize' => true,				//if enabled (true), image is resized (upsize keeping aspect ratio) with dimensions_max limits. If not dimensions_max is validated.
			],
			
			//avatar
			'avatar' => [
				'extend' => 'image',
				'max' => (1024 * 2),			//upload max size (2024 kb (2mb))
				'dimensions_max' => 256,
			],
			
			//logo
			'logo' => [
				'extend' => 'avatar',
				'dimensions_min' => 256,
				'dimensions_max' => 512,
			],
			
			//...custom types
		],
    ],
	
	//(deprecated)
	'ncms_mailer' => [
		'debug' => env('MAIL_DEBUG', true),
		'from' => [
			'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
			'name' => env('MAIL_FROM_NAME', 'Example'),
		],
		'mailer' => env('MAIL_MAILER', 'smtp'),
		'sendmail' => env('SENDMAIL_PATH'),
		'attachment_max_size' => (int) env('MAIL_ATTACHMENT_MAX_SIZE', 5), //MB
		'mime_pre' => env('MAIL_MIME_PRE'),
		'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
		'port' => (int) env('MAIL_PORT', 587),
		'username' => env('MAIL_USERNAME'),
		'password' => env('MAIL_PASSWORD'),
		'security' => env('MAIL_ENCRYPTION', 'tls'),
		'auth' => (bool) env('MAIL_AUTH', (env('MAIL_USERNAME') || env('MAIL_PASSWORD'))),
		'auth_type' => env('MAIL_AUTH_MODE', 'LOGIN'), //CRAM-MD5, LOGIN, PLAIN, XOAUTH2
		'timeout' => (int) env('MAIL_TIMEOUT', 120), //sec
		'timelimit' => (int) env('MAIL_TIMELIMIT', 120), //sec
	],
];
