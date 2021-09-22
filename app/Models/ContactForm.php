<?php

namespace App\Models;

use App\ContactFormModel;


class ContactForm extends ContactFormModel
{
	/**
	 * Model table.
	 *
	 * @var string
	 */
	protected $table = 'contact_forms';

	/**
	 * Model reference.
	 *
	 * @var string
	 */
	protected $model_ref = 'contact_form';

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
		'name' => ['required', 'string', 'max:64', 'min:3'],
		'email' => ['required', 'string', 'max:32', 'email'],
		'phone' => ['required', 'string', 'phone_number'],
		'message' => ['required', 'string'],
	];

	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = ['id'];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'email', 'phone', 'message'];
}