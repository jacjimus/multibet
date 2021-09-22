<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;

class UserModel extends BaseUser implements MustVerifyEmail
{
    //traits
    use Notifiable;

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

    //avatar accessor
    public function getAvatar()
    {
        if ($path = x_tstr($this->avatar)) {
            $store = app('UploadService')->store();
            if ($store->exists($path)) {
                return $store->url($path);
            }
        }
    }

    //send password reset notification
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPassword($token));
    }

    //send email verification notification
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmail);
    }

    //send phone_number verification notification
    public function sendPhoneVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyPhoneNumber);
    }
}
