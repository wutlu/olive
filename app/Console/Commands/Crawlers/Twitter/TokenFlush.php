<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;

use App\Mail\ServerAlertMail;

use System;

use Mail;

use App\Models\Twitter\Token;

class TokenFlush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:token_flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ölü işlemlerin meşgul ettiği tokenları serbest bırakır.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tokens = Token::whereNotNull('pid')->where('status', 'on')->get();

        if (count($tokens))
        {
            foreach ($tokens as $token)
            {
                if (!$token->pid)
                {
                    Mail::queue(new ServerAlertMail('Token Serbest Bırakıldı: ['.$token->id.']', $token->sh.PHP_EOL.PHP_EOL.$token->value));

                    $token->status  = 'off';
                    $token->pid     = null;
                    $token->sh      = null;
                    $token->value   = null;
                    $token->save();
                }
            }
        }
    }
}
