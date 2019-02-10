<?php

namespace App\Console\Commands\Server;

use Illuminate\Console\Command;

use Mail;
use App\Mail\ServerAlertMail;
use Illuminate\Support\Facades\Redis as RedisCache;

use Term;

class UserControl extends Command
{
    private $alias;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:user_control';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sunucuya bağlı makineleri e-posta ile yöneticilere bildir.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->alias = implode(':', [ str_slug(config('app.name')), 'security', 'server', 'online-users' ]);

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $each = true;

        while ($each == true)
        {
            $lines = explode(PHP_EOL, trim(shell_exec('who -q')));
            $users = explode(' ', $lines[0]);

            if (count($users))
            {
                $lines = array_map(function($item) {
                    return '- '.$item;
                }, explode(PHP_EOL, trim(shell_exec('who'))));

                $body = implode(PHP_EOL, $lines);

                if (RedisCache::get($this->alias) != md5($body))
                {
                    Mail::queue(new ServerAlertMail('Sunucuda Online ['.count($users).']', $body));

                    echo Term::line('Sunucuda Online ['.count($users).']->sent');
                }
                else
                {
                    echo Term::line('Sunucuda Online ['.count($users).']');
                }
            }
            else
            {
                echo Term::line('Sunucuda Online [0]');

                $body = null;
            }

            RedisCache::set($this->alias, md5($body));

            sleep(10);
        }
    }
}
