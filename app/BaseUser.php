<?php

namespace App;

use App\Traits\HasModelInfo;
use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

//NCMS: Base Auth User Model
class BaseUser extends Authenticatable implements JWTSubject
{
    //traits
    use PivotEventTrait, HasFactory, HasModelInfo;

    //override constructor
    public function __construct(array $attributes=[])
    {
        //HasModelInfo - set attrs
        $this->setInput($attributes);
        $this->setUid();

        //parent construct
        parent::__construct($attributes);
    }

    //jwt identifier
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    //jwt custom claims
    public function getJWTCustomClaims()
    {
        return [];
    }
}
