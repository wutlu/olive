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
        /**
         * Kurulum sağlandıktan sonra .env dosyasından
         * FIRT_MIGRATION alanı true olarak güncellenmeli.
         */
        if (config('app.first_migration'))
        {
            /**
             * Organizasyon sahibi olan kullanıcılara
             * her gün belirlenen saatlerde ödeme bildirimi e-postası gönder.
             */
            foreach (['00:00', '09:00', '15:00', '20:00'] as $time)
            {
                $schedule->command('check:upcoming_payments')
                         ->dailyAt($time)
                         ->timezone(config('app.timezone'))
                         ->withoutOverlapping();
            }

            /**
             * Kaynak Tespiti
             */
            $schedule->command('nohup "media:detector" --type=start')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();
            $schedule->command('nohup "shopping:detector" --type=start')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();

            /**
             * Kaynak Toplama
             */
            $schedule->command('nohup "media:taker" --type=start')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();
            $schedule->command('nohup "shopping:taker" --type=start')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();

            /**
             * Medya botları için kontrol aralığı belirle.
             */
            $schedule->command('nohup "media:minuter" --type=start')->hourly()->timezone(config('app.timezone'))->withoutOverlapping();

            /**
             * Veritabanındaki döküman sayılarını SQL\'e alır.
             */
            $schedule->command('nohup "update:crawler_counts" --type=start')->hourly()->timezone(config('app.timezone'))->withoutOverlapping();

            /**
             * Olağanüstü sunucu durumlarını e-posta gönder.
             */
            $schedule->command('alarm:control')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping();

            /**
             * Pinleme için PDF çıktı modülünün tetiklenmesi.
             */
            $schedule->command('trigger:pdf:pin_groups')->everyMinute()->timezone(config('app.timezone'));

            /**
             * Sözlük botlarının Tetiklenmesi
             */
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

            /**
             * Trendlerin hazırlanması.
             */
            # [ gerçek zamanlı trend ] #
            $schedule->command('nohup "trend:update --module=sozluk --period=live" --type=restart')->everyTenMinutes()->timezone(config('app.timezone'));
            $schedule->command('nohup "trend:update --module=news --period=live" --type=restart')->everyMinute()->timezone(config('app.timezone'));
            $schedule->command('nohup "trend:update --module=youtube --period=live" --type=restart')->everyMinute()->timezone(config('app.timezone'));

            # [ arşiv trend ] #
            $schedule->command('nohup "trend:update --module=sozluk --period=daily" --type=restart')->dailyAt('23:00')->timezone(config('app.timezone'));
            $schedule->command('nohup "trend:update --module=sozluk --period=weekly" --type=restart')->weeklyOn(7, '23:00')->timezone(config('app.timezone'));

            # [ arşiv trend ] #
            $schedule->command('nohup "trend:update --module=news --period=daily" --type=restart')->dailyAt('23:00')->timezone(config('app.timezone'));
            $schedule->command('nohup "trend:update --module=news --period=weekly" --type=restart')->weeklyOn(7, '23:00')->timezone(config('app.timezone'));

            # [ arşiv trend ] #
            $schedule->command('nohup "trend:update --module=youtube --period=daily" --type=restart')->dailyAt('23:00')->timezone(config('app.timezone'));
            $schedule->command('nohup "trend:update --module=youtube --period=weekly" --type=restart')->weeklyOn(7, '23:00')->timezone(config('app.timezone'));

            # [ gerçek trend ] #
            $schedule->command('nohup "trend:update --module=twitter" --type=restart')->everyTenMinutes()->timezone(config('app.timezone'));
            $schedule->command('nohup "trend:update --module=google" --type=restart')->hourly()->timezone(config('app.timezone'));

            /**
             * YouTube botlarının tetiklenmesi.
             */
            $option = Option::where('key', 'youtube.status')->where('value', 'on')->exists();

            if ($option)
            {
                $schedule->command('nohup "youtube:video_detect --type=trends" --type=start')
                         ->everyTenMinutes()
                         ->timezone(config('app.timezone'))
                         ->withoutOverlapping();

                $schedule->command('nohup "youtube:video_detect --type=followed_videos" --type=start')
                         ->hourly()
                         ->timezone(config('app.timezone'))
                         ->withoutOverlapping();

                $schedule->command('nohup "youtube:video_detect --type=followed_keywords" --type=start')
                         ->hourly()
                         ->timezone(config('app.timezone'))
                         ->withoutOverlapping();

                $schedule->command('nohup "youtube:video_detect --type=followed_channels" --type=start')
                         ->hourly()
                         ->timezone(config('app.timezone'))
                         ->withoutOverlapping();
            }

            /**
             * Google botlarının tetiklenmesi.
             */
            $option = Option::where('key', 'google.status')->where('value', 'on')->exists();

            if ($option)
            {
                $schedule->command('nohup "google:trend_detect" --type=restart')
                         ->hourly()
                         ->timezone(config('app.timezone'));
            }

            /**
             * Otomatik index modülü.
             */
            $options = Option::whereIn('key', [
                'twitter.index.auto',
                'youtube.index.auto',
            ])->where('value', 'on')->get();

            if (count($options))
            {
                foreach ($options as $option)
                {
                    switch ($option->key)
                    {
                        case 'twitter.index.auto': $key = 'twitter.tweets'; break;
                        case 'youtube.index.auto': $key = 'youtube.comments'; break;
                    }

                    $schedule->command('elasticsearch:auto_index --type='.$key)
                             ->hourly()
                             ->timezone(config('app.timezone'))
                             ->withoutOverlapping();
                }
            }

            /**
             * Twitter Tweet Modülü
             */
            $option = Option::where('key', 'twitter.status')->where('value', 'on')->exists();

            if ($option)
            {
                $schedule->command('nohup "twitter:stream:update --type=user" --type=restart')
                         ->everyMinute()
                         ->timezone(config('app.timezone'))
                         ->withoutOverlapping();

                $schedule->command('nohup "twitter:stream:update --type=keyword" --type=restart')
                         ->everyMinute()
                         ->timezone(config('app.timezone'))
                         ->withoutOverlapping();

                $schedule->command('nohup "twitter:stream:update --type=trend" --type=restart')
                         ->hourly()
                         ->timezone(config('app.timezone'))
                         ->withoutOverlapping();
            }

            $schedule->command('nohup "twitter:stream:trigger" --type=start')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping();

            /**
             * Proxy Durum Testleri
             */
            $schedule->command('nohup "proxy:check" --type=restart')
                     ->hourly()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping();

            /**
             * Veri sayılarının Redis'e alınması.
             */
            $schedule->command('nohup "redis:store --part=total_document" --type=restart')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping();

            /**
             * Forum uyarıları e-posta kuyruğu.
             */
            $schedule->command('nohup "forum:notification_trigger" --type=restart')
                     ->everyFiveMinutes()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping();

            /**
             * E-posta bülteni e-posta kuyruğu.
             */
            $schedule->command('nohup "newsletter:process_trigger" --type=restart')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping();

            /**
             * Başarılı olamayan eylemlerin tekrar kuyruğu.
             */
            $schedule->command('nohup "queue:retry all" --type=restart')
                     ->hourly()
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
