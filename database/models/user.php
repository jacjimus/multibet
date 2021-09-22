<?php
return [
	//Model
	'name' => 'user',
	'description' => 'Model for system users.',

	//Fields
	'fields' => [
		
		//id (primary key)
		'id' => 'NCMS_FIELD_ID',

		//type
		'type' => [
			'type' => 'NCMS_FIELD_STRING_16',
			'rules' => ['in:root,basic'],
			'default' => config('auth.user_type'),
			'fillable' => true,
		],
		
		//name
		'name' => [
			'type' => 'NCMS_FIELD_STRING_64',
			'rules' => ['min:3'],
			'fillable' => true,
		],
		
		//username
		'username' => [
			'type' => 'NCMS_FIELD_STRING_32',
			'rules' => [
				'min:4',
				'required_without_all:email,phone_number',
				'unique:users,username,NULL,NULL',
			],
			'nullable' => true,
			'fillable' => true,
		],

		//email
		'email' => [
			'type' => 'NCMS_FIELD_STRING_32',
			'rules' => [
				'email',
				'required_without_all:username,phone_number',
				'unique:users,email,NULL,NULL',
			],
			'nullable' => true,
			'fillable' => true,
		],

		//email verification
		'email_verified_at' => [
			'type' => 'NCMS_FIELD_TIMESTAMP',
			'nullable' => true,
		],

		//phone number
		'phone_number' => [
			'type' => 'NCMS_FIELD_STRING_16',
			'rules' => [
				'phone_number:NULL,phone_region',
				'required_without_all:username,email',
				'unique:users,phone_number,NULL,NULL',
			],
			'nullable' => true,
			'fillable' => true,
		],

		//phone region code (KE)
		'phone_region' => [
			'type' => 'NCMS_FIELD_CHAR_2',
			'rules' => [
				'region_code',
				'required_with:phone_number',
			],
			'nullable' => true,
			'fillable' => true,
		],

		//phone verification
		'phone_verified_at' => [
			'type' => 'NCMS_FIELD_TIMESTAMP',
			'nullable' => true,
		],

		//password
		'password' => [
			'type' => 'NCMS_FIELD_STRING',
			'rules' => config('auth.password_rules'),
			'nullable' => true,
			'fillable' => true,
			'hidden' => true,
		],

		//remember token
		'remember_token' => [
			'type' => 'NCMS_FIELD_REMEMBER_TOKEN',
			'hidden' => true,
		],
		
		//temp token
		'temp_token' => [
			'type' => 'NCMS_FIELD_STRING',
			'nullable' => true,
			'hidden' => true,
		],
		
		//avatar
		'avatar' => [
			'type' => 'NCMS_FIELD_STRING',
			'rules' => ['upload_file:avatar'],
			'nullable' => true,
			'fillable' => true,
		],
		
		//user status (0=disabled, 1=active)
		'status' => [
			'type' => 'NCMS_FIELD_UNSIGNED_TINY_INT',
			'rules' => ['min:0', 'max:1'],
			'default' => config('auth.user_status'),
			'fillable' => true,
		],

		//timestamps
		'timestamps' => 'NCMS_FIELD_TIMESTAMPS',
		
		//timestamp user
		'timestamp_user' => 'NCMS_FIELD_TIMESTAMP_USER',

		//softdelete
		'softdelete' => 'NCMS_FIELD_SOFTDELETE',
		
		//softdelete user
		'softdelete_user' => 'NCMS_FIELD_SOFTDELETE_USER',
	],
	
	//Relations
	'relations' => [
		
		//allows
		'morphMany' => [
			'allows' => 'allow',
		],
	],

	//Access
	'access' => [
	
		//reading
		'read' => [
		
			//public (other users)
			'public' => [
				//read allowed fields
				'allow' => [
					'name',
					'username',
				],
			],

			//auth (authenticated non-root users)
			'auth' => [
				//read allowed fields
				'allow' => [
					'id',
					'name',
					'username',
					'email',
					'phone_number',
					'phone_region',
				],
			],

			//user (self)
			'user' => [
				//reject reading fields
				'reject' => [
					'type',
					'email_verified_at',
					'phone_verified_at',
					'remember_token',
					'status',
				],
			],

			//all - allow where
			'allow_where' => ['status', '=', '1'], //status = active
		],

		//updating
		'update' => [
			//user (self)
			'user' => [
				//reject updating fields
				'reject' => [
					'type',
					'email_verified_at',
					'phone_verified_at',
					'remember_token',
					'status',
				],
			],

			//all - allow where
			'allow_where' => ['status', '=', '1'], //status active
		],

		//deleting
		'delete' => [
			//user (self)
			'user' => true,

			//all - allow where
			'allow_where' => ['status', '=', '1'], //status active
		],
	],
];
