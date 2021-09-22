<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Allow extends BaseModel
{
    use SoftDeletes;

    /**
     * Model table.
     *
     * @var string
     */
    protected $table = 'allows';

    /**
     * Model reference.
     *
     * @var string
     */
    protected $model_ref = 'allow';

    /**
     * Model options.
     *
     * @var array
     */
    protected $options = [
        'morphable' => true,
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
                'allow' => ['id', 'access', 'allow'],
            ],
        ],
    ];

    /**
     * Model validation rules.
     *
     * @var array
     */
    protected $rules = [
        'access' => ['required', 'string', 'max:128', 'permission'],
        'allow' => ['sometimes', 'boolean'],
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
    protected $fillable = ['access', 'allow'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function allowable()
    {
        return $this->morphTo();
    }
}
