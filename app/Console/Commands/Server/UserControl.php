<?php

namespace App\Console\Commands\Server;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis as RedisCache;

use Term;
use Mail;
use App\Mail\ServerAlertMail;

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
    protected $description = 'Sunucuya bağlı cihazları e-posta ile yöneticilere bildir.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->alias = implode(':', [ config('system.db.alias'), 'security', 'server', 'online-users' ]);

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
            $users = explode(PHP_EOL, trim(shell_exec('who')));

            if (trim(shell_exec('who')))
            {
                $lines = array_map(function($item) {
                    return '- '.$item;
                }, $users);

                $body = implode(PHP_EOL, $lines);

                if (RedisCache::get($this->alias) != md5($body))
                {
                    if (config('app.env') == 'production')
                    {
                        Mail::queue(new ServerAlertMail('Sunucuda Online ['.count($users).']', $body));
                    }

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
