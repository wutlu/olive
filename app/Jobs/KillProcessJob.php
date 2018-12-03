<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class KillProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $pid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $pid)
    {
        $this->pid = $pid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cmd = implode(' ', [
            'kill',
            '-9',
            $this->pid,
            '>>',
            '/dev/null',
            '2>&1',
            '&',
            'echo $!'
        ]);

        $pid = trim(shell_exec($cmd));
    }
}
