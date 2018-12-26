<?php

use Illuminate\Database\Seeder;

class ForumMessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\Forum\Message::class, 20)->create();
    }
}
