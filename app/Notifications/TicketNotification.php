<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subject;
    protected $message;
    protected $ticket_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $subject, string $message, int $ticket_id)
    {
        $this->subject   = $subject;
        $this->message   = $message;
        $this->ticket_id = $ticket_id;
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
                    ->greeting($this->subject)
                    ->line($this->message)
                    ->level('success')
                    ->action('Talebi Gör', route('settings.support.ticket', [ 'id' => $this->ticket_id ]));
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
