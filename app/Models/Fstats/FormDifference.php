<?php

namespace App\Models\Fstats;

use App\BaseModel;

class FormDifference extends BaseModel
{
    /**
     * Model table.
     *
     * @var string
     */
    protected $table = 'form_differences';

    /**
     * Model reference.
     *
     * @var string
     */
    protected $model_ref = 'fstats/fs_match';

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
        'home_team' => ['required', 'string'],
        'away_team' => ['required', 'string'],
        'last_five' => ['float'],
        'home_team_points' => ['required', 'float'],
        'away_team_points' => ['required', 'float'],
        ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}
