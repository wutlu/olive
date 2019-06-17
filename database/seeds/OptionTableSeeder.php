<?php

use Illuminate\Database\Seeder;

use App\Models\Option;

class OptionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Option::firstOrCreate( [ 'key' => 'email_alerts.server'                 ], [ 'value' => date('Y-m-d H:i:s')                   ] );
        Option::firstOrCreate( [ 'key' => 'email_alerts.log'                    ], [ 'value' => date('Y-m-d H:i:s')                   ] );

        Option::firstOrCreate( [ 'key' => 'root_alert.support'                  ], [ 'value' => 0                                     ] );

        Option::firstOrCreate( [ 'key' => 'youtube.status'                      ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'youtube.index.videos'                ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'youtube.index.auto'                  ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'youtube.index.comments'              ], [ 'value' => date('Y.m', strtotime('-1 month'))    ] );

        Option::firstOrCreate( [ 'key' => 'twitter.index.auto'                  ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'twitter.index.tweets'                ], [ 'value' => date('Y.m', strtotime('-1 month'))    ] );
        Option::firstOrCreate( [ 'key' => 'twitter.status'                      ], [ 'value' => 'off'                                 ] );

        Option::firstOrCreate( [ 'key' => 'media.index.status'                  ], [ 'value' => 'off'                                 ] );

        Option::firstOrCreate( [ 'key' => 'trend.status.news'                   ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.google'                 ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.twitter_tweet'          ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.twitter_hashtag'        ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.youtube_video'          ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.sozluk'                 ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.forum'                  ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.blog'                   ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.instagram'              ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.facebook'               ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.index'                         ], [ 'value' => 'off'                                 ] );

        Option::firstOrCreate( [ 'key' => 'data.learn'                          ], [ 'value' => 'off'                                 ] );

        Option::firstOrCreate( [ 'key' => 'unit_price.data_twitter'                      ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_sozluk'                       ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_news'                         ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_youtube_video'                ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_youtube_comment'              ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_shopping'                     ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_forum'                        ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_facebook'                     ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_instagram'                    ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_blog'                         ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.real_time_group_limit'             ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.alarm_limit'                       ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.pin_group_limit'                   ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.saved_searches_limit'              ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.historical_days'                   ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_youtube_channel_limit'   ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_youtube_video_limit'     ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_youtube_keyword_limit'   ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_twitter_keyword_limit'   ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_twitter_user_limit'      ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_facebook_keyword_limit'  ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_facebook_user_limit'     ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_instagram_keyword_limit' ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_instagram_user_limit'    ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_real_time'                  ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_search'                     ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_trend'                      ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_alarm'                      ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_pin'                        ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_model'                      ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_forum'                      ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'formal.discount_with_year'                    ], [ 'value' => 0                                     ] );
    }
}
