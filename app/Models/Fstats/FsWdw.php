<?php

namespace App\Models\Fstats;

use App\BaseModel;

class FsWdw extends BaseModel
{
    /**
     * Model table.
     *
     * @var string
     */
    protected $table = 'fstats_fs_wdws';

    /**
     * Model reference.
     *
     * @var string
     */
    protected $model_ref = 'fstats/fs_wdw';

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
        'h2h_url' => ['required', 'string'],
        'fixture' => ['required', 'string'],
        'home_win' => ['required', 'numeric'],
        'away_win' => ['required', 'numeric'],
        'draw_win' => ['required', 'numeric'],
        'home_odds' => ['required', 'numeric'],
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
