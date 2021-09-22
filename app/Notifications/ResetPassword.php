<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends Notification
{
    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $reset_url = url(config('app.url') . '/password-reset/' . $this->token . '/' . urlencode(base64_encode($notifiable->email)));

        return (new MailMessage)
        -> line('You are receiving this email because we received a password reset request for your account.')
       ->action('Reset Password', $reset_url)
       ->line('If you did not request a password reset, no further action is required.');
    }
}
