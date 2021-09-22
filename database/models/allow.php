<?php
return [
	//Model
	'name' => 'allow',
	'description' => 'Model for system access (morph) permissions.',

	//Fields
	'fields' => [
		//id (primary key)
		'id' => 'NCMS_FIELD_ID',

		//access (i.e. models.users.read)
		'access' => [
			'type' => 'NCMS_FIELD_STRING_128',
			'rules' => ['permission'],
			'fillable' => true,
		],
		
		//allow access (default false)
		'allow' => [
			'type' => 'NCMS_FIELD_BOOLEAN',
			'default' => '0',
			'fillable' => true,
		],

		//morph
		'allowable' => 'NCMS_FIELD_MORPH',
		
		//timestamps
		'timestamps' => 'NCMS_FIELD_TIMESTAMPS',
		
		//timestamp user
		'timestamp_user' => 'NCMS_FIELD_TIMESTAMP_USER',

		//softdelete
		'softdelete' => 'NCMS_FIELD_SOFTDELETE',
		
		//softdelete user
		'softdelete_user' => 'NCMS_FIELD_SOFTDELETE_USER',
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
					'access',
					'allow',
				],
			],
		],
	],
];