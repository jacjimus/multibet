<?php
return [
	//Model
	'name' => 'fs_match',
	'description' => 'Model for fstats footystats.org matches.',
	
	//Fields
	'fields' => [
		//id (primary key)
		'id' => 'NCMS_FIELD_ID',
		
		//correlated wdw id
		'fs_wdw_id' => [
			'type' => 'NCMS_FIELD_UNSIGNED_BIG_INT',
			'nullable' => true,
		],

		//correlated sp match id
		'sp_match_id' => [
			'type' => 'NCMS_FIELD_UNSIGNED_BIG_INT',
			'nullable' => true,
		],

		//date
		'date' => [
			'type' => 'NCMS_FIELD_UNSIGNED_BIG_INT',
		],

		//league name
		'league_name' => [
			'type' => 'NCMS_FIELD_STRING',
		],

		//league url
		'league_url' => [
			'type' => 'NCMS_FIELD_STRING',
			'nullable' => true,
		],
		
		//country
		'country' => [
			'type' => 'NCMS_FIELD_STRING_64',
		],
		
		//competition id
		'comp_id' => [
			'type' => 'NCMS_FIELD_UNSIGNED_INT',
		],
		
		//h2h url (match url)
		'h2h_url' => [
			'type' => 'NCMS_FIELD_STRING',
		],
		
		//time
		'time' => [
			'type' => 'NCMS_FIELD_UNSIGNED_BIG_INT',
		],
		
		//home id
		'home_id' => [
			'type' => 'NCMS_FIELD_UNSIGNED_INT',
		],
		
		//home url
		'home_url' => [
			'type' => 'NCMS_FIELD_STRING',
		],
		
		//home name
		'home_name' => [
			'type' => 'NCMS_FIELD_STRING',
		],
		
		//home form - last5 (home_form)
		'home_form_last5' => [
			'type' => 'NCMS_FIELD_FLOAT',
			'nullable' => true,
		],
		
		//home form - home_away
		'home_form_home_away' => [
			'type' => 'NCMS_FIELD_FLOAT',
			'nullable' => true,
		],
		
		//home score
		'home_score' => [
			'type' => 'NCMS_FIELD_UNSIGNED_INT',
			'nullable' => true,
		],

		//away id
		'away_id' => [
			'type' => 'NCMS_FIELD_UNSIGNED_INT',
		],

		//away url
		'away_url' => [
			'type' => 'NCMS_FIELD_STRING',
		],

		//away name
		'away_name' => [
			'type' => 'NCMS_FIELD_STRING',
		],

		//away form - last5 (away_form)
		'away_form_last5' => [
			'type' => 'NCMS_FIELD_FLOAT',
			'nullable' => true,
		],
		
		//away form - home_away
		'away_form_home_away' => [
			'type' => 'NCMS_FIELD_FLOAT',
			'nullable' => true,
		],

		//away score
		'away_score' => [
			'type' => 'NCMS_FIELD_UNSIGNED_INT',
			'nullable' => true,
		],

		//timestamps
		'timestamps' => 'NCMS_FIELD_TIMESTAMPS',
	],
];
