<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\Crawlers\SozlukCrawler;
use App\Jobs\Crawlers\Sozluk\TriggerJob as SozlukTriggerJob;

use App\Models\Option;

use App\Models\Twitter\Token;

use App\Utilities\SystemUtility;

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
                         ->withoutOverlapping(1);
            }

            /**
             * Alarmlar için E-posta gönder.
             */
            $schedule->command('nohup "alarm:trigger" --type=start')
                      ->everyMinute()
                      ->timezone(config('app.timezone'))
                      ->withoutOverlapping(1);

            /**
             * Kaynak Tespiti
             */
            $schedule->command('nohup "media:detector" --type=restart')
                      ->everyMinute()
                      ->timezone(config('app.timezone'));
            $schedule->command('nohup "shopping:detector" --type=restart')
                      ->everyMinute()
                      ->timezone(config('app.timezone'));

            /**
             * Kaynak Toplama
             */
            $schedule->command('nohup "media:taker" --type=restart')
                      ->everyMinute()
                      ->timezone(config('app.timezone'));
            $schedule->command('nohup "shopping:taker" --type=restart')
                      ->everyMinute()
                      ->timezone(config('app.timezone'));

            /**
             * Medya botları için kontrol aralığı belirle.
             */
            $schedule->command('nohup "media:minuter" --type=start')->hourly()->timezone(config('app.timezone'))->withoutOverlapping(1);

            /**
             * Veritabanındaki döküman sayılarını SQL\'e alır.
             */
            $schedule->command('nohup "update:crawler_counts" --type=start')->hourly()->timezone(config('app.timezone'))->withoutOverlapping(1);

            /**
             * Olağanüstü sunucu durumlarını e-posta gönder.
             */
            $schedule->command('alarm:control')->everyMinute()->timezone(config('app.timezone'))->withoutOverlapping(1);

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
                             ->withoutOverlapping(1);
                }
            }

            /**
             * Trendlerin hazırlanması.
             */
            # [ gerçek zamanlı trend ] #
            $schedule->command('nohup "trend:update --module=sozluk --period=live" --type=restart')
                     ->everyTenMinutes()
                     ->timezone(config('app.timezone'))
                     ->skip(function() {
                        return SystemUtility::option('trend.status.sozluk') != 'on';
                     });
            $schedule->command('nohup "trend:update --module=news --period=live" --type=restart')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->skip(function() {
                        return SystemUtility::option('trend.status.news') != 'on';
                     });
            $schedule->command('nohup "trend:update --module=youtube --period=live" --type=restart')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->skip(function() {
                        return SystemUtility::option('trend.status.youtube') != 'on';
                     });

            # [ arşiv trend ] #
            $schedule->command('nohup "trend:update --module=sozluk --period=daily" --type=restart')
                     ->dailyAt('23:45')
                     ->timezone(config('app.timezone'))
                     ->skip(function() {
                        return SystemUtility::option('trend.status.sozluk') != 'on';
                     });
            $schedule->command('nohup "trend:update --module=sozluk --period=weekly" --type=restart')
                     ->weekly()
                     ->sundays()
                     ->at('23:45')
                     ->timezone(config('app.timezone'))
                     ->skip(function() {
                        return SystemUtility::option('trend.status.sozluk') != 'on';
                     });

            # [ arşiv trend ] #
            $schedule->command('nohup "trend:update --module=news --period=daily" --type=restart')
                     ->dailyAt('23:45')
                     ->timezone(config('app.timezone'))
                     ->skip(function() {
                        return SystemUtility::option('trend.status.news') != 'on';
                     });
            $schedule->command('nohup "trend:update --module=news --period=weekly" --type=restart')
                     ->weekly()
                     ->sundays()
                     ->at('23:45')
                     ->timezone(config('app.timezone'))
                     ->skip(function() {
                        return SystemUtility::option('trend.status.news') != 'on';
                     });

            # [ arşiv trend ] #
            $schedule->command('nohup "trend:update --module=youtube --period=daily" --type=restart')
                     ->dailyAt('23:45')
                     ->timezone(config('app.timezone'))
                     ->skip(function() {
                        return SystemUtility::option('trend.status.youtube') != 'on';
                     });
            $schedule->command('nohup "trend:update --module=youtube --period=weekly" --type=restart')
                     ->weekly()
                     ->sundays()
                     ->at('23:45')
                     ->timezone(config('app.timezone'))
                     ->skip(function() {
                        return SystemUtility::option('trend.status.youtube') != 'on';
                     });

            # [ gerçek trend ] #
            $schedule->command('nohup "trend:update --module=twitter --type=live" --type=restart')
                     ->hourly()
                     ->timezone(config('app.timezone'))
                     ->skip(function() {
                        return SystemUtility::option('trend.status.twitter') != 'on';
                     });
            $schedule->command('nohup "trend:update --module=twitter --type=local" --type=restart')
                     ->everyFiveMinutes()
                     ->timezone(config('app.timezone'))
                     ->skip(function() {
                        return SystemUtility::option('trend.status.twitter') != 'on';
                     });
            $schedule->command('nohup "trend:update --module=google" --type=restart')
                     ->hourly()
                     ->timezone(config('app.timezone'))
                     ->skip(function() {
                        return SystemUtility::option('trend.status.google') != 'on';
                     });
            /**
             * Her gece 03:00'da takip edilecek aktif Twitter kullanıcılarını güncelle.
             */
            $schedule->command('nohup "twitter:follow_active_users" --type=restart')
                     ->dailyAt('03:00')
                     ->timezone(config('app.timezone'));

            /**
             * Medya siteleri dns adreslerinin toplanması.
             */
            $schedule->command('nohup "media:host" --type=restart')
                     ->dailyAt('03:00')
                     ->timezone(config('app.timezone'));

            /**
             * Medya siteleri alexa durumlarının belirlenmesi.
             */
            $schedule->command('nohup "media:alexa_ranker" --type=restart')
                     ->dailyAt('04:00')
                     ->timezone(config('app.timezone'));

            /**
             * YouTube botlarının tetiklenmesi.
             *
             * - Sabah 09:00 ile gece 01:00 arası her 15 dakikada bir.
             */
            $schedule->command('nohup "youtube:video_detect --type=trends" --type=restart')
                     ->unlessBetween('1:00', '9:00')
                     ->everyThirtyMinutes()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('youtube.status') != 'on';
                     });

            $schedule->command('nohup "youtube:video_detect --type=followed_videos" --type=restart')
                     ->unlessBetween('1:00', '9:00')
                     ->hourly()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('youtube.status') != 'on';
                     });

            $schedule->command('nohup "youtube:video_detect --type=followed_keywords" --type=restart')
                     ->unlessBetween('1:00', '9:00')
                     ->hourly()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('youtube.status') != 'on';
                     });

            $schedule->command('nohup "youtube:video_detect --type=followed_channels" --type=restart')
                     ->unlessBetween('1:00', '9:00')
                     ->hourly()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('youtube.status') != 'on';
                     });

            /**
             * Otomatik index modülü.
             */
            $schedule->command('elasticsearch:auto_index --type=twitter.tweets')
                     ->hourly()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('twitter.index.auto') != 'on';
                     });

            $schedule->command('elasticsearch:auto_index --type=youtube.comments')
                     ->hourly()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('youtube.index.auto') != 'on';
                     });

            /**
             * Twitter Tweet Modülü
             */
            $schedule->command('nohup "twitter:stream:update --type=user" --type=restart')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('twitter.status') != 'on';
                     });

            $schedule->command('nohup "twitter:stream:update --type=keyword" --type=restart')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('twitter.status') != 'on';
                     });

            $schedule->command('nohup "twitter:stream:update --type=trend" --type=restart')
                     ->hourly()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('twitter.status') != 'on';
                     });

            $schedule->command('nohup "twitter:stream:trigger" --type=start')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1);

            /**
             * Proxy Durum Testleri
             */
            $schedule->command('nohup "proxy:check" --type=restart')
                     ->hourly()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1);

            /**
             * Veri sayılarının Redis'e alınması.
             */
            $schedule->command('nohup "redis:store --part=total_document" --type=restart')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1);

            /**
             * Forum uyarıları e-posta kuyruğu.
             */
            $schedule->command('nohup "forum:notification_trigger" --type=restart')
                     ->everyFiveMinutes()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1);

            /**
             * E-posta bülteni e-posta kuyruğu.
             */
            $schedule->command('nohup "newsletter:process_trigger" --type=restart')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1);

            /**
             * Başarılı olamayan eylemlerin tekrar kuyruğu.
             */
            $schedule->command('nohup "queue:retry all" --type=restart')
                     ->hourly()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1);

            /**
             * Herhangi bir node kapanırsa yönetime e-posta gönder.
             */
            $schedule->command('nohup "elasticsearch:node_control" --type=restart')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1);
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
