<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends BaseModel
{
    use SoftDeletes;

    /**
     * Model table.
     *
     * @var string
     */
    protected $table = 'entries';

    /**
     * Model reference.
     *
     * @var string
     */
    protected $model_ref = 'entry';

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
            'public' => [
                'allow' => ['id', 'type', 'key', 'value'],
            ],
            'auth' => [
                'allow' => ['id', 'type', 'key', 'value'],
            ],
        ],
        'create' => 'inherit',
        'update' => 'inherit',
        'delete' => 'inherit',
    ];

    /**
     * Model validation rules.
     *
     * @var array
     */
    protected $rules = [
        'type' => ['required', 'string'],
        'key' => ['required', 'string'],
        'data' => ['nullable', 'string'],
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
    protected $hidden = ['data'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'key'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function entryable()
    {
        return $this->morphTo();
    }
}
