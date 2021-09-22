<?php

namespace App\Models;

use App\BasePivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleUser extends BasePivot
{
    use SoftDeletes;

    /**
     * Model table.
     *
     * @var string
     */
    protected $table = 'role_user';

    /**
     * Model reference.
     *
     * @var string
     */
    protected $model_ref = 'role_user';

    /**
     * Model options.
     *
     * @var array
     */
    protected $options = [
        'timestamps' => true,
        'timestamp_user' => true,
        'softdeletes' => true,
        'softdelete_user' => true,
    ];

    /**
     * Model validation rules.
     *
     * @var array
     */
    protected $rules = [
        'role_id' => ['required', 'integer', 'exists_or_null:roles,id'],
        'user_id' => ['required', 'integer', 'exists_or_null:users,id'],
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
    public function role()
    {
        return $this->belongsTo('App\Models\Role', 'role_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
