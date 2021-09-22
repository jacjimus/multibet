<?php
return [
	//Pivot
	'pivot' => true,
	'name' => 'role_user',
	'description' => 'Pivot for system role users.',

	//Fields
	'fields' => [
		//id (primary key)
		'id' => 'NCMS_FIELD_ID',

		//role id
		'role_id' => [
			'foreign' => ['role', 'id'],
		],

		//user id
		'user_id' => [
			'foreign' => ['user', 'id'],
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
];