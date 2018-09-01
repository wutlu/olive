<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        $this->subject = $subject;
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
        return $this->subject('Olive Uyarı: '.$this->subject)
                    ->markdown('emails.server.alert', [
                        'subject' => $this->subject,
                        'body' => $this->body
                    ])
                    ->to(config('mail.email_group'));
    }
}
