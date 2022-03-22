<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Leagues extends BaseModel
{
    /**
     * Model options.
     *
     * @var array
     */
    protected $options = [
        'timestamps' => true,
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'league_id' , 'country'];

    protected $rules = [
        'name' => ['required', 'string'],

    ];

    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixtures::class);
    }
}
