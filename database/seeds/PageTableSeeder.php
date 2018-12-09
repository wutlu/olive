<?php

use Illuminate\Database\Seeder;

use App\Models\Page;

class PageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Page::updateOrCreate(
        	[
        		'slug' => 'hakkimizda'
        	],
        	[
        		'title' => 'Hakkımızda',
        		'keywords' => null,
        		'description' => null,
        		'body' => 'Hakkımızda Sayfası!'
        	]
        );

        Page::updateOrCreate(
        	[
        		'slug' => 'iletisim'
        	],
        	[
        		'title' => 'İletişim',
        		'keywords' => null,
        		'description' => null,
        		'body' => 'İletişim Sayfası!'
        	]
        );

        Page::updateOrCreate(
        	[
        		'slug' => 'gizlilik-politikasi'
        	],
        	[
        		'title' => 'Gizlilik Politikası',
        		'keywords' => null,
        		'description' => null,
        		'body' => 'Gizlilik Politikası Sayfası!'
        	]
        );

        Page::updateOrCreate(
        	[
        		'slug' => 'kullanim-kosullari'
        	],
        	[
        		'title' => 'Kullanım Koşulları',
        		'keywords' => null,
        		'description' => null,
        		'body' => 'Kullanım Koşulları Sayfası!'
        	]
        );
    }
}
