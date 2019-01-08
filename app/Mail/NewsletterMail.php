<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewsletterMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $subject, string $body, string $email, string $queue = 'email')
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->email = $email;
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
                    ->to($this->email);
    }
}
