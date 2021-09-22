<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;

class VerifyPhoneNumber extends Notification implements ShouldQueue
{
    //traits
    use InteractsWithQueue, Queueable;

    //constructor
    public function __construct()
    {
        //..
    }

    //via channel
    public function via($notifiable)
    {
        #return [BongaSmsChannel::class];
    }

    //to SMS notification
    public function toSms($notifiable)
    {
        /*
        $message = 'Use the code ' . $this->_otp . ' to verify your phone number.';

        //TODO remove
        \App\Lib\System::log(json_encode([
            '$message' => $message,
            '$notifiable->otp' => $notifiable->otp,
            '$this->_otp' => $this->_otp,
        ]), 'verify_phone');

        return $message;
        */
    }
}
