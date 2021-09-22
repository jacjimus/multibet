<?php
return [
	//Model
	'name' => 'password_reset',
	'description' => 'Model for system password resets.',

	//Fields
	'fields' => [
		//email
		'email' => [
			'type' => 'NCMS_FIELD_STRING',
			'index' => true,
		],

		//token
		'token' => 'NCMS_FIELD_STRING',

		//created at timestamp
		'created_at' => [
			'type' => 'NCMS_FIELD_TIMESTAMP',
			'nullable' => true,
		],
	],
];