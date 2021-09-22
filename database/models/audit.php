<?php
return [
	//Model
	'name' => 'audit',
	'description' => 'Model for system audit (morph) values.',
	
	//Fields
	'fields' => [
		//id (primary key)
		'id' => 'NCMS_FIELD_ID',

		//audit action
		'action' => [
			'type' => 'NCMS_FIELD_STRING_64',
		],
		
		//user id
		'user_id' => [
			'foreign' => ['user', 'id'],
			'nullable' => true,
		],

		//auth user id
		'auth_user_id' => [
			'foreign' => ['user', 'id'],
			'nullable' => true,
		],

		//auth ip
		'auth_ip' => [
			'type' => 'NCMS_FIELD_IP_ADDRESS',
			'nullable' => true,
		],

		//auth useragent
		'auth_useragent' => [
			'type' => 'NCMS_FIELD_STRING_256',
			'nullable' => true,
		],

		//data model
		'data_model' => [
			'type' => 'NCMS_FIELD_STRING',
			'nullable' => true,
		],

		//data id
		'data_id' => [
			'type' => 'NCMS_FIELD_INT_ID',
			'nullable' => true,
		],

		//data before
		'data_before' => [
			'type' => 'NCMS_FIELD_JSON',
			'nullable' => true,
		],

		//data (after)
		'data_after' => [
			'type' => 'NCMS_FIELD_JSON',
			'nullable' => true,
		],

		//morph
		'auditable' => 'NCMS_FIELD_MORPH',

		//timestamps
		'timestamps' => 'NCMS_FIELD_TIMESTAMPS',

		//softdelete
		'softdelete' => 'NCMS_FIELD_SOFTDELETE',
	],

	//Access
	'access' => [
		//reading
		'read' => [
			//auth (authenticated non-root users)
			'auth' => [
				//read allowed fields
				'allow' => [
					'id',
					'action',
					'user',
					'authUser',
				],
			],
		],
	],
];