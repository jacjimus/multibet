<?php

namespace App\Models\Fstats;

use App\BaseModel;

class SpMatch extends BaseModel
{
    /**
     * Model table.
     *
     * @var string
     */
    protected $table = 'fstats_sp_matches';

    /**
     * Model reference.
     *
     * @var string
     */
    protected $model_ref = 'fstats/sp_match';

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
        'date' => ['required', 'integer', 'min:0'],
        'fs_match_id' => ['nullable', 'integer', 'exists_or_null:fstats_fs_matches,id'],
        'league_name' => ['required', 'string'],
        'country' => ['required', 'string', 'max:64'],
        'comp_id' => ['required', 'integer', 'min:0'],
        'match_id' => ['required', 'integer', 'min:0'],
        'sms_id' => ['required', 'integer', 'min:0'],
        'time' => ['required', 'integer', 'min:0'],
        'home_id' => ['required', 'integer', 'min:0'],
        'home_name' => ['required', 'string'],
        'home_odds' => ['required', 'numeric'],
        'away_id' => ['required', 'integer', 'min:0'],
        'away_name' => ['required', 'string'],
        'away_odds' => ['required', 'numeric'],
        'draw_odds' => ['required', 'numeric'],
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
    public function fstatsFsMatch()
    {
        return $this->belongsTo('App\Models\Fstats\FsMatch', 'fs_match_id', 'id');
    }
}
