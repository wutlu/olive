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
    public $authority;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $subject, string $body, string $authority = 'root', string $queue = 'email')
    {
        $this->subject = config('app.env') == 'local' ? implode(' ', [ '[LOCAL]', $subject ]) : $subject;
        $this->body = $body;
        $this->queue = $queue;
        $this->authority = $authority;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $query = User::where($this->authority, true)->get();

        if (count($query))
        {
            foreach ($query as $row)
            {
                UserActivityUtility::push(
                    $this->subject,
                    [
                        'key'            => implode('.', [ 'warning', $row->id ]),
                        'icon'           => 'warning',
                        'markdown'       => $this->body,
                        'user_id'        => $row->id,
                        'markdown_color' => '#ffebee'
                    ]
                );
            }
        }

        switch ($this->authority)
        {
            case 'root':
                $email = config('mail.root_email');
            break;
            case 'admin':
                $email = config('mail.admin_email');
            break;
            default:
                $email = config('mail.root_email');
            break;
        }

        return $this->subject($this->subject)
                    ->markdown('emails.server.alert', [
                        'subject' => $this->subject,
                        'body' => $this->body
                    ])
                    ->to($email);
    }
}
