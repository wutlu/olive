<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordValidationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user_id;
    protected $session_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(int $user_id, string $session_id)
    {
        $this->user_id = $user_id;
        $this->session_id = $session_id;
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
                    ->subject('Olive: Yeni şifrenizi oluşturun!')
                    ->greeting('Merhaba!')
                    ->level('olive')
                    ->line('Hesap şifrenizi unuttuğunuzu bildirdiniz.')
                    ->line('Aşağıdaki butona tıklayarak şifrenizi tekrar oluşturabilirsiniz.')
                    ->action('Yeni Şifre Oluşturun', route('user.password.new', [ 'user_id' => $this->user_id, 'session_id' => $this->session_id ]))
                    ->line('Bu işlemi siz gerçekleştirmediyseniz lütfen bu mesajı dikkate almayın.');
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
