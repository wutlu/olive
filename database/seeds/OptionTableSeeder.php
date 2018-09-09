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
        Option::updateOrCreate(
            [
                'key' => 'email_alerts.server'
            ],
            [
                'value' => date('Y-m-d H:i:s')
            ]
        );

        Option::updateOrCreate(
            [
                'key' => 'email_alerts.log'
            ],
            [
                'value' => date('Y-m-d H:i:s')
            ]
        );

        Option::updateOrCreate(
            [
                'key' => 'root_alert.support'
            ],
            [
                'value' => 0
            ]
        );
    }
}
