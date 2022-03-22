<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fixtures extends BaseModel
{
    protected $primaryKey = 'fixture_id';

    protected $guarded = 'id';

    protected $rules = [];

    /**
     * Model options.
     *
     * @var array
     */
    protected $options = [
        'timestamps' => true,
    ];

    public function league(): BelongsTo
    {
        return $this->belongsTo(Leagues::class, 'league_id', 'id');
    }
}
