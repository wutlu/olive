<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\User\User;
use App\Utilities\UserActivityUtility;

class ServerAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $subject, string $body, string $queue = 'email')
    {
        $this->subject = config('app.env') == 'local' ? implode(' ', [ '[LOCAL]', $subject ]) : $subject;
        $this->body = $body;
        $this->queue = $queue;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $roots = User::where('root', true)->get();

        if (count($roots))
        {
            foreach ($roots as $root)
            {
                UserActivityUtility::push(
                    $this->subject,
                    [
                        'key'            => implode('.', [ 'warning', $root->id ]),
                        'icon'           => 'warning',
                        'markdown'       => $this->body,
                        'user_id'        => $root->id,
                        'markdown_color' => '#d32f2f'
                    ]
                );
            }
        }

        return $this->subject('Olive Uyarı: '.$this->subject)
                    ->markdown('emails.server.alert', [
                        'subject' => $this->subject,
                        'body' => $this->body
                    ])
                    ->to(config('mail.email_group'));
    }
}
