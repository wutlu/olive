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
        Option::updateOrCreate( [ 'key' => 'email_alerts.server'      ], [ 'value' => date('Y-m-d H:i:s')                   ] );
        Option::updateOrCreate( [ 'key' => 'email_alerts.log'         ], [ 'value' => date('Y-m-d H:i:s')                   ] );
        Option::updateOrCreate( [ 'key' => 'root_alert.support'       ], [ 'value' => 0                                     ] );
        Option::updateOrCreate( [ 'key' => 'youtube.status'           ], [ 'value' => 'off'                                 ] );
        Option::updateOrCreate( [ 'key' => 'youtube.index.video'      ], [ 'value' => 'off'                                 ] );
        Option::updateOrCreate( [ 'key' => 'youtube.index.comment'    ], [ 'value' => 'off'                                 ] );
        Option::updateOrCreate( [ 'key' => 'google.status'            ], [ 'value' => 'off'                                 ] );
        Option::updateOrCreate( [ 'key' => 'google.index.search'      ], [ 'value' => 'off'                                 ] );
        Option::updateOrCreate( [ 'key' => 'twitter.index.auto'       ], [ 'value' => 'off'                                 ] );
        Option::updateOrCreate( [ 'key' => 'twitter.index.tweets'     ], [ 'value' => date('Y.m', strtotime('-1 month'))    ] );
        Option::updateOrCreate( [ 'key' => 'twitter.index.trends'     ], [ 'value' => 'off'                                 ] );
        Option::updateOrCreate( [ 'key' => 'twitter.trend.status'     ], [ 'value' => 'off'                                 ] );
        Option::updateOrCreate( [ 'key' => 'twitter.status'           ], [ 'value' => 'off'                                 ] );
    }
}
