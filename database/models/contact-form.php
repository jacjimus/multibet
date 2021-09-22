<?php
return [
	//Model
	'name' => 'contact_form',
	'description' => 'Model for contact form submissions.',

	//Fields
	'fields' => [
		//id (primary key)
		'id' => 'NCMS_FIELD_ID',
		
		//name
		'name' => [
			'type' => 'NCMS_FIELD_STRING_64',
			'rules' => ['min:3'],
			'fillable' => true,
		],
		
		//email
		'email' => [
			'type' => 'NCMS_FIELD_STRING_32',
			'rules' => ['email'],
			'fillable' => true,
		],
		
		//phone
		'phone' => [
			'type' => 'NCMS_FIELD_STRING',
			'rules' => ['phone_number'],
			'fillable' => true,
		],
		
		//message
		'message' => [
			'type' => 'NCMS_FIELD_TEXT',
			'fillable' => true,
		],

		//timestamps
		'timestamps' => 'NCMS_FIELD_TIMESTAMPS',
	],
];
