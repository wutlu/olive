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
    protected $organisation_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $name, int $id)
    {
        $this->name = $name;
        $this->organisation_id = $id;
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
                    ->subject('Olive: Organizasyon Oluşturuldu')
                    ->greeting('Tebrikler!')
                    ->line('Organizasyonunuz başarılı bir şekilde oluşturuldu.')
                    ->line('Sanal faturanız oluşturuldu. Ödemeniz gerçekleştikten sonra sanal faturanız, resmi fatura olarak güncellenecek ve organizasyon aktif hale gelecektir.')
                    ->level('success')
                    ->action('Faturanızı Görüntüleyin', route('organisation.invoice', $this->organisation_id))
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
