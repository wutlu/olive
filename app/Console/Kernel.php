<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\Crawlers\SozlukCrawler;
use App\Jobs\Crawlers\Sozluk\TriggerJob as SozlukTriggerJob;

use App\Models\Option;

use App\Models\Twitter\Token;

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
            # her pazartesi ödeme bildirimi gönderir
            # 
            $schedule->command('check:upcoming_payments')
                     ->mondays()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping();

            # 
            # toplanılmak üzere bağlantı tespit eder
            # 
            $schedule->command('nohup "media:detector" --type=start')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();
            $schedule->command('nohup "shopping:detector" --type=start')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();

            # 
            # bağlantı toplar
            # 
            $schedule->command('nohup "media:taker" --type=start')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();
            $schedule->command('nohup "shopping:taker" --type=start')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();

            # 
            # medya bağlantıları için kontrol aralığı belirleme
            # 
            $schedule->command('nohup "media:minuter" --type=start')->hourly()->timezone(config('app.timezone'))->withoutOverlapping();

            # 
            # sistem alarmı kontrolü
            # 
            $schedule->command('alarm:control')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();

            #
            # müşteri Twitter hesaplarının durumunu kontrol eder
            #
            $schedule->command('nohup "twitter:account_control" --type=restart')->hourly()->timezone(config('app.timezone'));

            #
            # pinleme pdf çıktılarını tetikler
            #
            $schedule->command('trigger:pdf:pin_groups')->everyMinute()->timezone(config('app.timezone'));

            /* ---------------------------------------- */


            /* ---------------------------------------- */

            $crawlers = SozlukCrawler::where('status', true)->get();

            if (count($crawlers))
            {
                foreach ($crawlers as $crawler)
                {
                    $schedule->job((new SozlukTriggerJob($crawler->id))->onQueue('trigger'))
                             ->everyMinute()
                             ->timezone(config('app.timezone'))
                             ->withoutOverlapping();
                }
            }

            /* ---------------------------------------- */

            $option = Option::where('key', 'youtube.status')->where('value', 'on')->exists();

            if ($option)
            {
                // yapılacak
            }

            /* ---------------------------------------- */

            $option = Option::where('key', 'google.status')->where('value', 'on')->exists();

            if ($option)
            {
                $schedule->command('nohup "google:trend_detect" --type=restart')
                         ->hourly()
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

            $option = Option::where('key', 'twitter.status')->where('value', 'on')->exists();

            if ($option)
            {
                $schedule->command('nohup "twitter:stream:update --type=user" --type=restart')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();
                $schedule->command('nohup "twitter:stream:update --type=keyword" --type=restart')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();
                $schedule->command('nohup "twitter:stream:update --type=trend" --type=restart')->hourly()->timezone(config('app.timezone'))->withoutOverlapping();
            }

            $schedule->command('nohup "twitter:stream:trigger" --type=start')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();

            /* ---------------------------------------- */

            $schedule->command('nohup "proxy:check"')
                     ->hourly()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping();

            /* ---------------------------------------- */

            $schedule->command('nohup "forum:notification_trigger"')
                     ->everyFiveMinutes()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping();

            /* ---------------------------------------- */

            $schedule->command('nohup "newsletter:process_trigger"')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping();
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
