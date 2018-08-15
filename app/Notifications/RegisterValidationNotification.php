<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RegisterValidationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user_id;
    protected $session_id;
    protected $name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(int $user_id, string $session_id, string $name)
    {
        $this->name       = $name;
        $this->user_id    = $user_id;
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
                    ->subject('Olive: E-posta adresinizi doğrulayın!')
                    ->greeting('Hoşgeldiniz, ' . $this->name)
                    ->level('olive')
                    ->line('Olive hesabınız başarılı bir şekilde oluşturuldu.')
                    ->line('Son olarak aşağıdaki butona tıklayarak e-posta adresinizi doğrulamanız gerekiyor.')
                    ->action('Doğrula', route('user.register.validate', [ 'user_id' => $this->user_id, 'session_id' => $this->session_id ]));
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
