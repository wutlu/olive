<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\Crawlers\SozlukCrawler;
use App\Models\Option;

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
        if (env('FIRST_MIGRATION'))
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
            $schedule->command('alarm:control')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();

            #
            # Müşteri Twitter hesaplarının aktifliğini her saat başı kontrol et.
            #
            $schedule->command('nohup "twitter:account_control" --type=restart')->hourly()->timezone(config('app.timezone'));

            /* ---------------------------------------- */

            $crawlers = SozlukCrawler::where('status', true)->get();

            if (count($crawlers))
            {
                foreach ($crawlers as $crawler)
                {
                    $schedule->command('nohup "sozluk:crawler '.$crawler->id.'" --type=start')
                             ->everyMinute()
                             ->timezone(config('app.timezone'))
                             ->withoutOverlapping();
                }
            }

            /* ---------------------------------------- */

            $option = Option::where('key', 'youtube.status')->where('value', 'on')->exists();

            if ($option)
            {
                $schedule->command('nohup "youtube:trend_detect" --type=restart')
                         ->everyFifteenMinutes()
                         ->timezone(config('app.timezone'));
            }

            /* ---------------------------------------- */

            $option = Option::where('key', 'google.status')->where('value', 'on')->exists();

            if ($option)
            {
                $schedule->command('nohup "google:trend_detect" --type=restart')
                         ->everyThirtyMinutes()
                         ->timezone(config('app.timezone'));
            }

            /* ---------------------------------------- */

            $option = Option::where('key', 'twitter.index.auto')->where('value', 'on')->exists();

            if ($option)
            {
                $schedule->command('twitter:auto_index')
                         ->everyFiveMinutes()
                         ->timezone(config('app.timezone'))
                         ->withoutOverlapping();
            }

            /* ---------------------------------------- */

            $option = Option::where('key', 'twitter.trend.status')->where('value', 'on')->exists();

            if ($option)
            {
                $schedule->command('twitter:trend_detect')
                         ->everyTenMinutes()
                         ->timezone(config('app.timezone'))
                         ->withoutOverlapping();
            }

            /* ---------------------------------------- */

            /* ---------------------------------------- */

            $option = Option::where('key', 'twitter.status')->first();

            if (@$option)
            {
                $status = $option->value == 'on' ? 'restart' : 'kill';

                $schedule->command('nohup "twitter:stream --type=trend" --type='.$status.'')
                         ->everyFifteenMinutes()
                         ->timezone(config('app.timezone'));
            }
        }
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
