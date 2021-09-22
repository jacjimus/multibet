<?php
return [
	//Model
	'name' => 'payment',
	'description' => 'Model for payments.',
	
	//Fields
	'fields' => [
		//id (primary key)
		'id' => 'NCMS_FIELD_ID',
		
		//user id
		'user_id' => [
			'foreign' => ['user', 'id'],
			'nullable' => true,
		],
		
		//ref
		'ref' => [
			'type' => 'NCMS_FIELD_STRING_64',
			'nullable' => true,
		],
		
		//date
		'date' => [
			'type' => 'NCMS_FIELD_TIMESTAMP',
		],
		
		//type
		'type' => [
			'type' => 'NCMS_FIELD_STRING_16',
		],
		
		//amount
		'amount' => [
			'type' => 'NCMS_FIELD_FLOAT',
		],
		
		//currency
		'currency' => [
			'type' => 'NCMS_FIELD_STRING_8',
		],
		
		//provider
		'provider' => [
			'type' => 'NCMS_FIELD_STRING_32',
		],
		
		//name
		'name' => [
			'type' => 'NCMS_FIELD_STRING_64',
		],
		
		//phone
		'phone' => [
			'type' => 'NCMS_FIELD_STRING_16',
			'nullable' => true,
		],
		
		//email
		'email' => [
			'type' => 'NCMS_FIELD_STRING_32',
			'nullable' => true,
		],
		
		//account
		'account' => [
			'type' => 'NCMS_FIELD_STRING_64',
			'nullable' => true,
		],
		
		//data
		'data' => [
			'type' => 'NCMS_FIELD_JSON',
			'nullable' => true,
		],

		//timestamps
		'timestamps' => 'NCMS_FIELD_TIMESTAMPS',
	],
];
