<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AlarmMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $data, string $queue = 'email')
    {
        $this->data = $data;
        $this->queue = $queue;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $alarm = $this->data['alarm'];

        return $this->subject(implode(' ', [ '🔔', $alarm->name, '🔔' ]))
                    ->markdown('emails.alarm', [
                        'data' => $this->data
                    ])
                    ->to($alarm->emails());
    }
}
