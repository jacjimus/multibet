<?php

namespace App\Models;

use App\BaseModel;

class Payment extends BaseModel
{
    /**
     * Model table.
     *
     * @var string
     */
    protected $table = 'payments';

    /**
     * Model reference.
     *
     * @var string
     */
    protected $model_ref = 'payment';

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
        'user_id' => ['nullable', 'integer', 'exists_or_null:users,id'],
        'ref' => ['nullable', 'string', 'max:64'],
        'type' => ['required', 'string', 'max:16'],
        'amount' => ['required', 'numeric'],
        'currency' => ['required', 'string', 'max:8'],
        'provider' => ['required', 'string', 'max:32'],
        'name' => ['required', 'string', 'max:64'],
        'phone' => ['nullable', 'string', 'max:16'],
        'email' => ['nullable', 'string', 'max:32'],
        'account' => ['nullable', 'string', 'max:64'],
        'data' => ['nullable', 'json'],
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
