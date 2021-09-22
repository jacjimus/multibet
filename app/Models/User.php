<?php

namespace App\Models;

use App\UserModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends UserModel
{
    use SoftDeletes;

    /**
     * Model table.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Model reference.
     *
     * @var string
     */
    protected $model_ref = 'user';

    /**
     * Model options.
     *
     * @var array
     */
    protected $options = [
        'pivots' => true,
        'timestamps' => true,
        'timestamp_user' => true,
        'softdeletes' => true,
        'softdelete_user' => true,
    ];

    /**
     * Model access rules.
     *
     * @var array
     */
    protected $access = [
        'read' => [
            'public' => [
                'allow' => ['name', 'username'],
            ],
            'auth' => [
                'allow' => ['id', 'name', 'username', 'email', 'phone_number', 'phone_region'],
            ],
            'user' => [
                'reject' => [
                    'type', 'email_verified_at', 'phone_verified_at', 'remember_token',
                    'status',
                ],
            ],
            'allow_where' => ['status', '=', '1'],
        ],
        'update' => [
            'user' => [
                'reject' => [
                    'type', 'email_verified_at', 'phone_verified_at', 'remember_token',
                    'status',
                ],
            ],
            'allow_where' => ['status', '=', '1'],
        ],
        'delete' => [
            'user' => true,
            'allow_where' => ['status', '=', '1'],
        ],
    ];

    /**
     * Model validation rules.
     *
     * @var array
     */
    protected $rules = [
        'password' => ['required', 'string', 'min:6'],

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
    protected $hidden = ['remember_token', 'temp_token'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'name', 'username', 'email', 'phone_number', 'phone_region', 'password', 'avatar', 'status'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany('App\Models\Role', 'role_user', 'user_id', 'role_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function audits()
    {
        return $this->hasMany('App\Models\Audit', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function authUserAudits()
    {
        return $this->hasMany('App\Models\Audit', 'auth_user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function oauthProviders()
    {
        return $this->hasMany('App\Models\OauthProvider', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany('App\Models\Payment', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function allows()
    {
        return $this->morphMany('App\Models\Allow', 'allowable');
    }
}
