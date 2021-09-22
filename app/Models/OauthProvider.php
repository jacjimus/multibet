<?php

namespace App\Models;

use App\BaseModel;

class OauthProvider extends BaseModel
{
    /**
     * Model table.
     *
     * @var string
     */
    protected $table = 'oauth_providers';

    /**
     * Model reference.
     *
     * @var string
     */
    protected $model_ref = 'oauth_provider';

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
        'user_id' => ['required', 'integer', 'exists_or_null:users,id'],
        'provider' => ['required', 'string'],
        'provider_user_id' => ['required', 'string'],
        'access_token' => ['required', 'string'],
        'refresh_token' => ['nullable', 'string'],
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['access_token', 'refresh_token'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['provider', 'provider_user_id', 'access_token', 'refresh_token'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
