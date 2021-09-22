<?php

namespace App;

class ContactFormModel extends BaseModel
{
    //name mutator
    public function setNameAttribute($name)
    {
        return $this->attributes['name'] = x_is_string($name, 1) ? ucwords(x_tstr($name)) : null;
    }

    //email mutator
    public function setEmailAttribute($email)
    {
        return $this->attributes['email'] = x_is_string($email, 1) ? strtolower(x_tstr($email)) : null;
    }
}
