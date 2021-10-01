<?php

namespace App\Models\Fstats;

use App\BaseModel;

class FsMatch extends BaseModel
{
    /**
     * Model table.
     *
     * @var string
     */
    protected $table = 'fstats_fs_matches';

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
        'fs_wdw_id' => ['nullable', 'integer', 'min:0'],
        'sp_match_id' => ['nullable', 'integer', 'min:0'],
        'date' => ['required', 'integer', 'min:0'],
        'league_name' => ['required', 'string'],
        'league_url' => ['nullable', 'string'],
        'country' => ['required', 'string', 'max:64'],
        'comp_id' => ['required', 'integer', 'min:0'],
        'h2h_url' => ['required', 'string'],
        'time' => ['required', 'integer', 'min:0'],
        'home_id' => ['required', 'integer', 'min:0'],
        'home_url' => ['required', 'string'],
        'home_name' => ['required', 'string'],
        'home_form_last5' => ['nullable', 'numeric'],
        'home_form_home_away' => ['nullable', 'numeric'],
        'home_score' => ['nullable', 'integer', 'min:0'],
        'away_id' => ['required', 'integer', 'min:0'],
        'away_url' => ['required', 'string'],
        'away_name' => ['required', 'string'],
        'away_form_last5' => ['nullable', 'numeric'],
        'away_form_home_away' => ['nullable', 'numeric'],
        'away_score' => ['nullable', 'integer', 'min:0'],
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function fstatsFsWdw()
    {
        return $this->hasOne(FsWdw::class)->withDefault();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function fstatsSpMatch()
    {
        return $this->hasOne('App\Models\Fstats\SpMatch', 'fs_match_id', 'id');
    }
}
