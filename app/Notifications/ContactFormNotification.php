<?php

namespace App\Notifications;

use App\Models\ContactForm;
use Exception;
use Illuminate\Auth\Notifications\VerifyEmail as Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class ContactFormNotification extends Notification implements ShouldQueue
{
    //traits
    use Queueable;

    /**
     * @var \App\Models\ContactForm
     */
    public $model;

    /**
     * Create a new notification instance.
     *
     * @param ContactForm $model
     *
     * @return void
     */
    public function __construct(ContactForm $model)
    {
        $this->model  = $model;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        //contact form model
        $model = $this->model;
        if (!($model instanceof ContactForm && $model->created_at)) {
            throw new Exception('Invalid contact form notification model!');
        }

        //contact form data
        $data = [
            'name' => $model->name,
            'email' => $model->email,
            'phone' => $model->phone,
            'message' => str_replace("\n", '<br>', str_replace("\r", '', trim($model->message))),
            'timestamp' => $model->created_at->toDateTimeString(),
        ];

        //mail message
        /*
        return (new MailMessage)
        -> replyTo($data['email'])
        -> subject(trans('message.contact-form-subject', $data))
        -> markdown('mail.contact-form', ['data' => $data]);
        */

        return (new MailMessage)
        -> replyTo($data['email'])
        -> subject(trans('message.contact-form-subject', $data))
        -> greeting(trans('message.contact-form-greeting', $data))
        -> line(new HtmlString(trans('message.contact-form-intro', $data)))
        -> line(new HtmlString(trans('message.contact-form-table', $data)))
        -> salutation(new HtmlString(trans('message.contact-form-salutation', $data)));
    }
}
