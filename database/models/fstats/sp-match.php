<?php
return [
	//Model
	'name' => 'sp_match',
	'description' => 'Model for fstats www.ke.sportpesa.com matches.',
	
	//Fields
	'fields' => [
		//id (primary key)
		'id' => 'NCMS_FIELD_ID',

		//date
		'date' => [
			'type' => 'NCMS_FIELD_UNSIGNED_BIG_INT',
		],

		//footystats match id
		'fs_match_id' => [
			'foreign' => ['fstats/fs_match', 'id'],
			'foreign_has_one' => true,
			'nullable' => true,
		],

		//league name
		'league_name' => [
			'type' => 'NCMS_FIELD_STRING',
		],
		
		//country
		'country' => [
			'type' => 'NCMS_FIELD_STRING_64',
		],
		
		//competition id
		'comp_id' => [
			'type' => 'NCMS_FIELD_UNSIGNED_INT',
		],

		//match id
		'match_id' => [
			'type' => 'NCMS_FIELD_UNSIGNED_INT',
		],

		//sms id
		'sms_id' => [
			'type' => 'NCMS_FIELD_UNSIGNED_INT',
		],

		//time
		'time' => [
			'type' => 'NCMS_FIELD_UNSIGNED_BIG_INT',
		],
		
		//home id
		'home_id' => [
			'type' => 'NCMS_FIELD_UNSIGNED_INT',
		],
		
		//home name
		'home_name' => [
			'type' => 'NCMS_FIELD_STRING',
		],
		
		//home odds
		'home_odds' => [
			'type' => 'NCMS_FIELD_FLOAT',
		],

		//away id
		'away_id' => [
			'type' => 'NCMS_FIELD_UNSIGNED_INT',
		],

		//away name
		'away_name' => [
			'type' => 'NCMS_FIELD_STRING',
		],

		//away odds
		'away_odds' => [
			'type' => 'NCMS_FIELD_FLOAT',
		],

		//draw odds
		'draw_odds' => [
			'type' => 'NCMS_FIELD_FLOAT',
		],

		//timestamps
		'timestamps' => 'NCMS_FIELD_TIMESTAMPS',
	],
];