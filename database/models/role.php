<?php
return [
	//Model
	'name' => 'role',
	'description' => 'Model for system roles.',

	//Fields
	'fields' => [
		//id (primary key)
		'id' => 'NCMS_FIELD_ID',

		//role type (root, basic)
		'type' => [
			'type' => 'NCMS_FIELD_STRING_16',
			'rules' => ['in:root,basic'],
			'fillable' => true,
		],
		
		//role name
		'name' => [
			'type' => 'NCMS_FIELD_STRING_64',
			'rules' => ['unique:roles,name,NULL,NULL'],
			'fillable' => true,
		],

		//role description
		'description' => [
			'type' => 'NCMS_FIELD_TEXT',
			'nullable' => true,
			'fillable' => true,
		],
		
		//role status (0=disabled, 1=active)
		'status' => [
			'type' => 'NCMS_FIELD_UNSIGNED_TINY_INT',
			'rules' => ['min:0', 'max:1'],
			'default' => config('auth.role_status'),
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
		//morph many
		'morphMany' => [
			'allows' => 'allow',
		],
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
					'name',
					'description',
				],
			],

			//all - allow where
			'allow_where' => ['status', '=', '1'], //status = active
		],
	],
];