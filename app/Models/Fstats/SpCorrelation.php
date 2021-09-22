<?php

namespace App\Models\Fstats;

use App\BaseModel;


class SpCorrelation extends BaseModel
{
	/**
	 * Model table.
	 *
	 * @var string
	 */
	protected $table = 'fstats_sp_correlations';

	/**
	 * Model reference.
	 *
	 * @var string
	 */
	protected $model_ref = 'fstats/sp_correlation';

	/**
	 * Model options.
	 *
	 * @var array
	 */
	protected $options = [
		'timestamps' => true,
	];

	/**
	 * Model validation rules.
	 *
	 * @var array
	 */
	protected $rules = [
		'type' => ['required', 'string'],
		'sp_name' => ['required', 'string'],
		'fs_name' => ['required', 'string'],
		'similarity' => ['required', 'numeric'],
		'sim_avg' => ['required', 'numeric'],
		'teams_avg' => ['required', 'numeric'],
	];

	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = ['id'];
}