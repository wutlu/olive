<?php

use Illuminate\Database\Seeder;

use App\Models\Setting;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::updateOrCreate(
        	[
        		'key' => 'email_alerts.server'
        	],
        	[
        		'value' => date('Y-m-d H:i:s')
        	]
        );
    }
}
