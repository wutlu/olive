<?php

use Illuminate\Database\Seeder;

use App\Models\Carousel;

class CarouselsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		Carousel::create(
        	[
				'title' => 'Bilgiyi Kaçırmayın',
				'description' => 'Gerçek zamanlı veri analiz modülünü denediniz mi?',
				'pattern' => 'sphere-1',
				'button_action' => route('realtime.stream'),
				'button_text' => 'Şimdi Deneyin'
        	]
        );
		Carousel::create(
        	[
				'title' => 'Birlikte Daha Güçlü',
				'description' => 'Kriter belirleyin, daha çok veri elde edelim.',
				'pattern' => 'sphere-3',
				'button_action' => route('twitter.keyword.list'),
				'button_text' => 'Veri Havuzu'
        	]
        );
    }
}
