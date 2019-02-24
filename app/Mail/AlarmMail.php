<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AlarmMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $emails;
    public $link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $subject, string $body, string $link, array $emails, string $queue = 'email')
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->emails = $emails;
        $this->link = $link;
        $this->queue = $queue;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->markdown('emails.newsletter', [
                        'subject' => $this->subject,
                        'body' => $this->body
                    ])
                    ->action('Olive ile İncele', $this->link);
                    ->to($this->email);
    }
}
