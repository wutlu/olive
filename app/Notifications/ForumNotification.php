<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ForumNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subject;
    protected $greeting;
    protected $message;
    protected $route;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $subject, string $greeting, string $message, string $route)
    {
        $this->route    = $route;
        $this->subject  = $subject;
        $this->message  = $message;
        $this->greeting = $greeting;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->subject)
                    ->greeting($this->greeting)
                    ->line($this->message)
                    ->level('success')
                    ->action('Konuya Git', $this->route);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
