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
            foreach ([ '11:00', '16:00' ] as $time)
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
             * Açık unutulan raporlar için e-posta bildirimi gönder.
             */
            $schedule->command('nohup "report:alert" --type=start')
                     ->everyThirtyMinutes()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1);

            /**
             * Borsa bilgilerini güncelle.
             */
            $schedule->command('nohup "borsa:update --type=xu030-bist-30" --type=start')
                     ->hourlyAt(15)
                     ->between('09:00', '19:00')
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1);

            /**
             * Borsa bilgilerini güncelle.
             */
            $schedule->command('nohup "borsa:counter" --type=start')
                     ->hourlyAt(45)
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1);

            /**
             * Popüler Trendleri kategorize et.
             */
            $schedule->command('nohup "trend:pop_categorization" --type=start')
                     ->dailyAt('07:00')
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1);

            /**
             * Twitter için takip edilen kullanıcıların hesap durumlarını kontrol et.
             */
            $schedule->command('nohup "twitter:user_status" --type=start')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1);

            /**
             * Instagram için Self bağlantılarının vakitlice tetiklenmesi.
             */
            $schedule->command('nohup "instagram:self:trigger" --type=restart')
                     ->everyMinute()
                     ->timezone(config('app.timezone'))
                     ->skip(function() {
                           return SystemUtility::option('instagram.status') != 'on';
                     });

            /**
             * Instagram için kontrol aralığı belirle.
             */
            $schedule->command('nohup "instagram:self:minuter" --type=start')->hourly()->timezone(config('app.timezone'))->withoutOverlapping(1);

            /**
             * Instagram bağlantılarının ne kadar veri topladığını sayar.
             */
            $schedule->command('nohup "instagram:self:counter" --type=start')->daily()->timezone(config('app.timezone'))->withoutOverlapping(1);

            /**
             * Kaynak Tespiti
             */
            $schedule->command('nohup "media:detector" --type=restart')
                     ->everyMinute()
                     ->timezone(config('app.timezone'));
            $schedule->command('nohup "shopping:detector" --type=restart')
                     ->everyMinute()
                     ->timezone(config('app.timezone'));
            $schedule->command('nohup "blog:detector" --type=restart')
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
            $schedule->command('nohup "blog:taker" --type=restart')
                     ->everyMinute()
                     ->timezone(config('app.timezone'));

            /**
             * Medya botları için kontrol aralığı belirle.
             */
            $schedule->command('nohup "media:minuter" --type=start')->hourly()->timezone(config('app.timezone'))->withoutOverlapping(1);

            /**
             * Blog botları için kontrol aralığı belirle.
             */
            $schedule->command('nohup "blog:minuter" --type=start')->hourly()->timezone(config('app.timezone'))->withoutOverlapping(1);

            /**
             * Yeni domainler tespit eder.
             */
            $schedule->command('nohup "domain:detector" --type=start')->hourly()->timezone(config('app.timezone'))->withoutOverlapping(1);

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

            foreach ([
                'trend.status.twitter_tweet' => 'twitter_tweet',
                'trend.status.twitter_favorite' => 'twitter_favorite',
                'trend.status.twitter_hashtag' => 'twitter_hashtag',
                'trend.status.instagram_hashtag' => 'instagram_hashtag',
                'trend.status.news' => 'news',
                'trend.status.blog' => 'blog',
                'trend.status.sozluk' => 'entry',
                'trend.status.youtube_video' => 'youtube_video' ] as $key => $module)
            {
                if ($module == 'youtube_video')
                {
                    $schedule->command('nohup "trend:detect --module=youtube_video --time=\"-1 hours\" --redis=1" --type=restart')
                             ->everyThirtyMinutes()
                             ->timezone(config('app.timezone'))
                             ->skip(function() use($key) {
                                return SystemUtility::option('trend.status.youtube_video') != 'on';
                             });
                }
                else if ($module == 'twitter_favorite')
                {
                    $schedule->command('nohup "trend:detect --module=twitter_favorite --time=\"-1 hours\" --redis=1" --type=restart')
                             ->everyMinute()
                             ->timezone(config('app.timezone'))
                             ->skip(function() use($key) {
                                return SystemUtility::option('trend.status.youtube_video') != 'on';
                             });
                }
                else
                {
                    $schedule->command('nohup "trend:detect --module='.$module.' --time=\"-10 minutes\" --redis=1" --type=restart')
                             ->everyMinute()
                             ->timezone(config('app.timezone'))
                             ->skip(function() use($key) {
                                return SystemUtility::option($key) != 'on';
                             });
                }

                $schedule->command('nohup "trend:detect --module='.$module.' --time=\"-1 hours\" --insert=1" --type=restart')
                         ->hourlyAt('59')
                         ->timezone(config('app.timezone'))
                         ->skip(function() use($key) {
                            return SystemUtility::option($key) != 'on';
                         });

                $schedule->command('nohup "trend:detect --module='.$module.' --time=\"-1 days\" --insert=1" --type=restart')
                         ->dailyAt('23:55')
                         ->timezone(config('app.timezone'))
                         ->skip(function() use($key) {
                            return SystemUtility::option($key) != 'on';
                         });

                $schedule->command('nohup "trend:detect --module='.$module.' --time=\"-7 days\" --insert=1" --type=restart')
                         ->weeklyOn(7, '23:45')
                         ->timezone(config('app.timezone'))
                         ->skip(function() use($key) {
                            return SystemUtility::option($key) != 'on';
                         });

                $schedule->command('nohup "trend:detect --module='.$module.' --time=\"-1 months\" --insert=1" --type=restart')
                         ->monthlyOn(30, '22:45')
                         ->timezone(config('app.timezone'))
                         ->skip(function() use($key) {
                            return SystemUtility::option($key) != 'on';
                         });
            }

            $schedule->command('nohup "trend:detect --module=google --time=\"-1 hours\" --insert=1 --redis=1" --type=restart')
                     ->everyFifteenMinutes()
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
             * Blog siteleri dns adreslerinin toplanması.
             */
            $schedule->command('nohup "blog:host" --type=restart')
                     ->dailyAt('04:00')
                     ->timezone(config('app.timezone'));

            /**
             * Medya siteleri alexa durumlarının belirlenmesi.
             */
            $schedule->command('nohup "media:alexa_ranker" --type=restart')
                     ->dailyAt('04:00')
                     ->timezone(config('app.timezone'));

            /**
             * Blog siteleri alexa durumlarının belirlenmesi.
             */
            $schedule->command('nohup "blog:alexa_ranker" --type=restart')
                     ->dailyAt('05:00')
                     ->timezone(config('app.timezone'));

            /**
             * YouTube botlarının tetiklenmesi.
             *
             * - Sabah 09:00 ile gece 01:00 arası her saat başı.
             */
            $schedule->command('nohup "youtube:video_detect --type=trends" --type=restart')
                     //->unlessBetween('1:00', '9:00')
                     ->hourly()
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('youtube.status') != 'on';
                     });

            $schedule->command('nohup "youtube:video_detect --type=followed_videos" --type=restart')
                     ->dailyAt('12:00')
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('youtube.status') != 'on';
                     });

            $schedule->command('nohup "youtube:video_detect --type=followed_keywords" --type=restart')
                     ->dailyAt('02:00')
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('youtube.status') != 'on';
                     });

            $schedule->command('nohup "youtube:video_detect --type=followed_channels" --type=restart')
                     ->dailyAt('01:00')
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('youtube.status') != 'on';
                     });

            /**
             * Duygu Öğrenimi
             */
            $schedule->command('nohup "sentiment:learn" --type=restart')
                     ->dailyAt('23:00')
                     ->timezone(config('app.timezone'))
                     ->withoutOverlapping(1)
                     ->skip(function() {
                        return SystemUtility::option('data.learn') != 'on';
                     });

            /**
             * Otomatik index modülü.
             */
            foreach ([
                'instagram' => 'medias',
                'twitter' => 'tweets',
                'youtube' => 'comments'
            ] as $key => $option)
            {
                $schedule->command('elasticsearch:auto_index --type='.$key.'.'.$option)
                         ->daily()
                         ->timezone(config('app.timezone'))
                         ->withoutOverlapping(1)
                         ->skip(function() use ($key) {
                            return SystemUtility::option($key.'.index.auto') != 'on';
                         });
            }

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
                     ->hourlyAt('10')
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
                     ->everyTenMinutes()
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

            $schedule->command('nohup "currency:update" --type=restart')
                     ->hourlyAt('58')
                     ->timezone(config('app.timezone'));

            $schedule->command('nohup "crypto:update" --type=restart')
                     ->hourlyAt('58')
                     ->timezone(config('app.timezone'));
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
