<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Crawlers\SozlukCrawler;

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
        # Site Takipçileri.
        # 
        $schedule->command('media:detector')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();
        $schedule->command('shopping:detector')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();

        # 
        # Bağlantı Toplayıcıları
        # 
        $schedule->command('media:taker')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();
        $schedule->command('shopping:taker')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();

        # 
        # Alarmları sürekli kontrol et.
        # 
        $schedule->command('alarm:control')
                 ->everyMinute()
                 ->timezone(config('app.timezone'))
                 ->withoutOverlapping();

        /* ---------------------------------------- */

        $crawlers = SozlukCrawler::where('status', true)->get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                $schedule->command('nohup "sozluk:crawler '.$crawler->id.'"')
                         ->everyMinute()
                         ->timezone(config('app.timezone'))
                         ->withoutOverlapping();
            }
        }

        /* ---------------------------------------- */
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
