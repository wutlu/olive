<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SendPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $name;
    protected $password;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $name, string $password)
    {
        $this->name = $name;
        $this->password = $password;
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
        $message[] = '| Kullanıcı Adı   | Şifre                  |';
        $message[] = '| --------------: | :--------------------- |';
        $message[] = '| '.$this->name.' | '.$this->password.'    |';

        return (new MailMessage)
                ->subject('Olive: Hesap Bilgileriniz')
                ->greeting('Merhaba,')
                ->line('Olive giriş bilgileriniz aşağıdadır. Güvenliğiniz için, oturum açtıktan sonra şifrenizi güncelleyin.')
                ->with([
                    'table' => implode(PHP_EOL, $message)
                ])
                ->line('Giriş yapmak için aşağıdaki bağlantıyı kullanın:')
                ->line('['.route('user.login').']('.route('user.login').')');
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
