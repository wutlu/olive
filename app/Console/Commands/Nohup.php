<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;

class Nohup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nohup {cmd} {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Arkaplanda işlem çalıştırır.';

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
        $sh = $this->argument('cmd');

        $key = implode('/', [ 'processes', md5($sh) ]);

        $file = Storage::exists($key) ? json_decode(Storage::get($key)) : null;

        $process_id = $file ? (posix_getpgid($file->pid) ? $file->pid : null) : null;

        $type = $this->option('type');

        $types = [
            'start' => 'Start Process',
            'restart' => 'Restart Process',
            'kill' => 'Kill Process'
        ];

        if (!$type)
        {
            $type = $this->choice('What would you like to do?', $types, $type);
        }

        if ($type == 'kill' || $type == 'restart')
        {
            if ($process_id)
            {
                $this->error(self::kill($process_id));
            }
            else
            {
                $this->error('Process is not running.');
            }
        }

        if ($type == 'start' || $type == 'restart')
        {
            sleep(1);

            $process_id = $file ? (posix_getpgid($file->pid) ? $file->pid : null) : null;

            if ($process_id)
            {
                $this->info('['.$process_id.'] process already running.');
            }
            else
            {
                $this->info(self::start($key, $sh));
            }
        }
    }

    # kill
    public function kill(int $process_id)
    {
        $cmd = implode(' ', [
            'kill',
            '-9',
            $process_id,
            '>>',
            '/dev/null',
            '2>&1',
            '&',
            'echo $!'
        ]);

        $pid = trim(shell_exec($cmd));

        return '['.$process_id.'] process killed. ('.$pid.')';
    }

    # start
    public function start(string $key, string $sh)
    {
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

        Storage::put($key, json_encode([ 'pid' => trim($pid), 'command' => $sh ]));

        return '['.$pid.'] process started.';
    }
}
