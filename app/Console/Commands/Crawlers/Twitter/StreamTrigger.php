<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;

use App\Models\Twitter\Token;
use App\Models\Option;

use Storage;

class StreamTrigger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:stream:trigger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Twitter, gerçek zamanlı akış tetikle.';

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
        $tokens = Token::whereIn('status', [ 'on', 'restart', 'stop', 'start' ])->get();

        if (count($tokens))
        {
            foreach ($tokens as $token)
            {
                $this->line('Token: ['.$token->id.']');

                $clear         = false;
                $stop_trigger  = false;
                $start_trigger = false;

                $option = Option::where('key', 'twitter.status')->where('value', 'on')->exists();

                if ($option)
                {
                    switch ($token->status)
                    {
                        case 'on':
                            if (!$token->pid)
                            {
                                $start_trigger = true;
                            }
                        break;
                        case 'restart':
                            $stop_trigger  = true;
                            $start_trigger = true;
                        break;
                        case 'stop':
                            $clear         = true;
                            $stop_trigger  = true;
                        break;
                        case 'start':
                            $start_trigger = true;
                        break;
                    }
                }
                else
                {
                    $clear        = true;
                    $stop_trigger = true;
                }

                if ($stop_trigger)
                {
                    $this->error('Stopping...');

                    $cmd = implode(' ', [
                        'kill',
                        '-9',
                        $token->pid,
                        '>>',
                        '/dev/null',
                        '2>&1',
                        '&',
                        'echo $!'
                    ]);

                    $pid = trim(shell_exec($cmd));

                    $this->error('Process Killed: ['.$token->pid.']');

                    if ($clear)
                    {
                        $token->status  = 'off';
                        $token->pid     = null;
                        $token->type    = null;
                        $token->tmp_key = null;
                        $token->value   = null;
                    }
                }

                if ($start_trigger)
                {
                    $this->info('Starting...');

                    sleep(1);

                    $sh = 'twitter:stream:process --tokenId='.$token->id;

                    $key = implode('/', [ 'processes', md5($sh) ]);
                    $cmd = implode(' ', [
                        'nohup',
                        'php',
                        base_path('artisan'),
                        $sh,
                        '>>',
                        '/dev/null',
                        '2>&1',
                        '&',
                        'echo $!'
                    ]);

                    $pid = trim(shell_exec($cmd));

                    Storage::put(
                        $key,
                        json_encode(
                            [
                                'pid' => trim($pid),
                                'command' => $sh
                            ]
                        )
                    );

                    $this->info('['.$sh.'] process started.');

                    $token->status = 'on';

                }

                $token->update();

                sleep(1);
            }
        }
        else
        {
            $this->error('Eylem yapılacak token bulunamadı.');
        }
    }
}
