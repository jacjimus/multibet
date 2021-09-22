<?php
return [
	//Model
	'name' => 'sp_correlation',
	'description' => 'Model for fstats sportpesa - footystats correlations.',
	
	//Fields
	'fields' => [
		//id (primary key)
		'id' => 'NCMS_FIELD_ID',

		//type
		'type' => [
			'type' => 'NCMS_FIELD_STRING',
		],

		//sportpesa name
		'sp_name' => [
			'type' => 'NCMS_FIELD_STRING',
		],

		//footystats name
		'fs_name' => [
			'type' => 'NCMS_FIELD_STRING',
		],

		//similarity
		'similarity' => [
			'type' => 'NCMS_FIELD_FLOAT',
		],

		//match average similarity
		'sim_avg' => [
			'type' => 'NCMS_FIELD_FLOAT',
		],

		//teams average similarity
		'teams_avg' => [
			'type' => 'NCMS_FIELD_FLOAT',
		],

		//timestamps
		'timestamps' => 'NCMS_FIELD_TIMESTAMPS',
	],
];