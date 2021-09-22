<?php
return [
	//Model
	'name' => 'oauth_provider',
	'description' => 'Model for system oauth providers.',

	//Fields
	'fields' => [
		//id (primary key)
		'id' => 'NCMS_FIELD_ID',

		//user id
		'user_id' => [
			'foreign' => ['user', 'id'],
		],

		//provider
		'provider' => [
			'type' => 'NCMS_FIELD_STRING',
			'fillable' => true,
		],
		
		//provider user_id
		'provider_user_id' => [
			'type' => 'NCMS_FIELD_STRING',
			'fillable' => true,
		],
		
		//access token
		'access_token' => [
			'type' => 'NCMS_FIELD_STRING',
			'hidden' => true,
			'fillable' => true,
		],
		
		//refresh token
		'refresh_token' => [
			'type' => 'NCMS_FIELD_STRING',
			'nullable' => true,
			'hidden' => true,
			'fillable' => true,
		],

		//timestamps
		'timestamps' => 'NCMS_FIELD_TIMESTAMPS',
	],
];
