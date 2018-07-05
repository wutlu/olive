<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OrganisationWasCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $name)
    {
        $this->name = $name;
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
                    ->subject('Olive: Organizasyonunuz Oluşturuldu!')
                    ->greeting('Merhaba, '.$this->name)
                    ->level('olive')
                    ->line('Ödemeniz başarılı bir şekilde gerçekleştirildi.')
                    ->line('Fatura bilgileriniz e-posta adresinize gönderilecektir.')
                    ->line('Teşekkürler.');
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
