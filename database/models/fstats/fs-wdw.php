<?php
return [
	//Model
	'name' => 'fs_wdw',
	'description' => 'Model for fstats footystats.org win-draw-win entries.',
	
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

		//h2h url (match url)
		'h2h_url' => [
			'type' => 'NCMS_FIELD_STRING',
		],

		//fixture (home vs away)
		'fixture' => [
			'type' => 'NCMS_FIELD_STRING',
		],

		//home - win percentage
		'home_win' => [
			'type' => 'NCMS_FIELD_FLOAT',
		],

		//away - win percentage
		'away_win' => [
			'type' => 'NCMS_FIELD_FLOAT',
		],

		//draw - win percentage
		'draw_win' => [
			'type' => 'NCMS_FIELD_FLOAT',
		],

		//home odds
		'home_odds' => [
			'type' => 'NCMS_FIELD_FLOAT',
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