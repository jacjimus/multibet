<?php

namespace App;

use App\Traits\HasModelInfo;
use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;

//NCMS: Base Pivot Model
class BasePivot extends Pivot
{
    //traits
    use PivotEventTrait, HasModelInfo;

    //override constructor
    public function __construct(array $attributes=[])
    {
        //HasModelInfo - set attrs
        $this->setInput($attributes);
        $this->setUid();

        //parent construct
        parent::__construct($attributes);
    }
}
