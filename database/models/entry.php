<?php
return [
	//Model
	'name' => 'entry',
	'description' => 'Model for system entry (morph) values.',

	//Fields
	'fields' => [
		//id (primary key)
		'id' => 'NCMS_FIELD_ID',

		//entry type
		'type' => [
			'type' => 'NCMS_FIELD_STRING',
			'fillable' => true,
		],
		
		//entry key
		'key' => [
			'type' => 'NCMS_FIELD_STRING',
			'fillable' => true,
		],
		
		//entry data
		'data' => [
			'type' => 'NCMS_FIELD_TEXT',
			'nullable' => true,
			'hidden' => true,
		],

		//morph
		'entryable' => 'NCMS_FIELD_MORPH',

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
			//public (other users)
			'public' => [
				//read allowed fields
				'allow' => [
					'id',
					'type',
					'key',
					'value',
				],
			],

			//auth (authenticated non-root users)
			'auth' => [
				//read allowed fields
				'allow' => [
					'id',
					'type',
					'key',
					'value',
				],
			],
		],

		//creating
		'create' => 'inherit', //inherit create access from morphable

		//updating
		'update' => 'inherit', //inherit create access from morphable

		//deleting
		'delete' => 'inherit', //inherit delete access from morphable
	],
];