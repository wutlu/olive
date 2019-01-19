<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class LoginNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $name;
    protected $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $name, string $data)
    {
        $this->name = $name;
        $this->data = $data;
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
                    ->subject('Olive: Hesabınıza giriş yapıldı!')
                    ->greeting('Merhaba, ' . $this->name)
                    ->level('olive')
                    ->line('Aşağıdaki bilgilere sahip bir cihazdan oturum açıldı.')
                    ->with([
                        'table' => $this->data
                    ]);
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
