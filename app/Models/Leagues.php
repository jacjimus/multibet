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
    protected $primaryKey = 'league_id';

    public $incrementing = false;

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

    public function getFirstNameAttribute(): string
    {
        return "{$this->name} ({$this->country})";
    }

    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixtures::class);
    }
}
