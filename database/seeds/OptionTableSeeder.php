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
        Option::firstOrCreate( [ 'key' => 'email_alerts.server'      ], [ 'value' => date('Y-m-d H:i:s')                   ] );
        Option::firstOrCreate( [ 'key' => 'email_alerts.log'         ], [ 'value' => date('Y-m-d H:i:s')                   ] );
        Option::firstOrCreate( [ 'key' => 'root_alert.support'       ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'root_alert.partner'       ], [ 'value' => 0                                     ] );
        Option::firstOrCreate( [ 'key' => 'youtube.status'           ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'youtube.index.videos'     ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'youtube.index.auto'       ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'youtube.index.comments'   ], [ 'value' => date('Y.m', strtotime('-1 month'))    ] );
        Option::firstOrCreate( [ 'key' => 'google.status'            ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'google.index.search'      ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'twitter.index.auto'       ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'twitter.index.tweets'     ], [ 'value' => date('Y.m', strtotime('-1 month'))    ] );
        Option::firstOrCreate( [ 'key' => 'twitter.index.trends'     ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'twitter.trend.status'     ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'twitter.status'           ], [ 'value' => 'off'                                 ] );
        Option::firstOrCreate( [ 'key' => 'media.index.status'       ], [ 'value' => 'off'                                 ] );
    }
}
