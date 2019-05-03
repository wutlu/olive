<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OrganisationWasUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $name;
    protected $organisation_id;

    private $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $name, int $id)
    {
        $this->name = $name;
        $this->organisation_id = $id;

        foreach (config('formal.banks') as $key => $bank)
        {
            $this->data[] = '('.$key.') '.$bank['iban'];
        }
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
                    ->subject('Olive: Ödeme Bekliyor')
                    ->greeting('Merhaba, '.$this->name)
                    ->line('Organizasyonunuz başarılı bir şekilde oluşturuldu. Şimdi ödeme yapmanız gerekiyor.')
                    ->line('Havale/EFT yapmak için aşağıdaki IBAN numaralarını kullanabilirsiniz.')
                    ->line('Havale/EFT durumunda lütfen açıklama kısmına organizasyon numaranızı ('.$this->organisation_id.') belirtin.')
                    ->line('Size daha hızlı yanıt verebilmemiz adına "Destek" sayfamızdan ödeme bildirimi yapabilirsiniz.')
                    ->line('Online ödeme yapmak için lütfen aşağıdaki bağlantıya tıklayın.')
                    ->line(implode(PHP_EOL, $this->data))
                    ->level('success')
                    ->action('Online Ödeme', route('organisation.invoice.payment'))
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
