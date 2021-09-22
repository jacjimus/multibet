<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Audit extends BaseModel
{
    use SoftDeletes;

    /**
     * Model table.
     *
     * @var string
     */
    protected $table = 'audits';

    /**
     * Model reference.
     *
     * @var string
     */
    protected $model_ref = 'audit';

    /**
     * Model options.
     *
     * @var array
     */
    protected $options = [
        'morphable' => true,
        'timestamps' => true,
        'softdeletes' => true,
    ];

    /**
     * Model access rules.
     *
     * @var array
     */
    protected $access = [
        'read' => [
            'auth' => [
                'allow' => ['id', 'action', 'user', 'authUser'],
            ],
        ],
    ];

    /**
     * Model validation rules.
     *
     * @var array
     */
    protected $rules = [
        'action' => ['required', 'string', 'max:64'],
        'user_id' => ['nullable', 'integer', 'exists_or_null:users,id'],
        'auth_user_id' => ['nullable', 'integer', 'exists_or_null:users,id'],
        'auth_ip' => ['nullable', 'ip'],
        'auth_useragent' => ['nullable', 'string', 'max:256'],
        'data_model' => ['nullable', 'string'],
        'data_id' => ['nullable', 'integer', 'min:1'],
        'data_before' => ['nullable', 'json'],
        'data_after' => ['nullable', 'json'],
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function authUser()
    {
        return $this->belongsTo('App\Models\User', 'auth_user_id', 'id');
    }
}
