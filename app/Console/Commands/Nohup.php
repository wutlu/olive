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
    protected $signature = 'nohup {cmd}';

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

        if ($process_id)
        {
            $this->error('['.$process_id.'] process zaten çalışıyor.');
        }
        else
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

            $this->info('['.$pid.'] process başlatıldı.');
        }
    }
}
