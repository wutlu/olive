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

        Option::firstOrCreate( [ 'key' => 'instagram.status'                    ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'instagram.index.users'               ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'instagram.index.auto'                ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'instagram.index.medias'              ], [ 'value' => date('Y.m', strtotime('-1 month'))    ] );

        Option::firstOrCreate( [ 'key' => 'media.index.status'                  ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'blog.index.status'                   ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'newspaper.index.status'              ], [ 'value' => 'off'                                 ] );

        Option::firstOrCreate( [ 'key' => 'trend.status.news'                   ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.google'                 ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.twitter_tweet'          ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.twitter_favorite'       ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.twitter_hashtag'        ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.youtube_video'          ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.sozluk'                 ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.blog'                   ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.instagram_hashtag'      ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.status.facebook'               ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'trend.index'                         ], [ 'value' => 'off'                                 ] );

        Option::firstOrCreate( [ 'key' => 'data.learn'                          ], [ 'value' => 'off'                                 ] );

        Option::firstOrCreate( [ 'key' => 'unit_price.data_twitter'                      ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_sozluk'                       ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_news'                         ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_youtube_video'                ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_youtube_comment'              ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_shopping'                     ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_facebook'                     ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_instagram'                    ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_blog'                         ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.archive_limit'                     ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.saved_searches_limit'              ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.historical_days'                   ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_youtube_channel_limit'   ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_youtube_video_limit'     ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_youtube_keyword_limit'   ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_twitter_keyword_limit'   ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_twitter_user_limit'      ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_facebook_keyword_limit'  ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_facebook_user_limit'     ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.data_pool_instagram_follow_limit'  ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_real_time'                  ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_crm'                        ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_search'                     ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_trend'                      ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_alarm'                      ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_compare'                    ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_borsa'                      ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.module_report'                     ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'unit_price.user'                              ], [ 'value' => 0                                     ] );

        Option::firstOrCreate( [ 'key' => 'formal.discount_with_year'                    ], [ 'value' => 0                                     ] );

        Option::firstOrCreate( [ 'key' => 'formal.partner.eagle.percent'                 ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'formal.partner.phoenix.percent'               ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'formal.partner.gryphon.percent'               ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'formal.partner.dragon.percent'                ], [ 'value' => 0                                     ] );
    }
}
