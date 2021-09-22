<?php

namespace App\Models;

use App\BaseModel;


class PasswordReset extends BaseModel
{
	/**
	 * Model table.
	 *
	 * @var string
	 */
	protected $table = 'password_resets';

	/**
	 * Model reference.
	 *
	 * @var string
	 */
	protected $model_ref = 'password_reset';

	/**
	 * Model validation rules.
	 *
	 * @var array
	 */
	protected $rules = [
		'email' => ['required', 'string'],
		'token' => ['required', 'string'],
	];
}