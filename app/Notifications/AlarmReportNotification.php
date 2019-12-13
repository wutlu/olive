<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use App\Models\User\User;
use App\Models\Alarm;
use App\Models\Report;

class AlarmReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $alarm;
    protected $user;
    protected $report;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Alarm $alarm, User $user, Report $report)
    {
        $this->alarm  = $alarm;
        $this->user   = $user;
        $this->report = $report;
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
                    ->subject('Rapor Hazır!')
                    ->greeting('Merhaba, '.$this->user->name.'. Raporunuzu hazırladık!')
                    ->level('olive')
                    ->line('Aşağıdaki butona tıklayarak veya hesabınızdan "Raporlar" bölümüne giderek inceleyebilirsiniz.')
                    ->line('Raporu açmak için **'.$this->report->password.'** şifresini kullanın.')
                    ->action('Rapor', route('report.view', [ 'key' => $this->report->key ]));
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
