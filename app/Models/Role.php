<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends BaseModel
{
    use SoftDeletes;

    /**
     * Model table.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * Model reference.
     *
     * @var string
     */
    protected $model_ref = 'role';

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
            'auth' => [
                'allow' => ['id', 'name', 'description'],
            ],
            'allow_where' => ['status', '=', '1'],
        ],
    ];

    /**
     * Model validation rules.
     *
     * @var array
     */
    protected $rules = [
        'type' => ['required', 'string', 'max:16', 'in:root,basic'],
        'name' => ['required', 'string', 'max:64', 'unique:roles,name,NULL,NULL'],
        'description' => ['nullable', 'string'],
        'status' => ['sometimes', 'integer', 'min:0', 'max:1'],
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
    protected $fillable = ['type', 'name', 'description', 'status'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'role_user', 'role_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function allows()
    {
        return $this->morphMany('App\Models\Allow', 'allowable');
    }
}
