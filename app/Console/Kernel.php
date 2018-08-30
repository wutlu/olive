<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        # 
        # Her pazartesi ödeme bildirimi gönder.
        # 
        $schedule->command('check:upcoming_payments')
                 ->mondays()
                 ->timezone(config('app.timezone'))
                 ->withoutOverlapping();

        # 
        # Medya sitelerini sürekli takip et.
        # 
        $schedule->command('media:link_detect')
                 ->everyMinute()
                 ->timezone(config('app.timezone'))
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
