<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Elasticsearch\Document;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
$json = array (
  'body' => 
  array (
    52 => 
    array (
      'create' => 
      array (
        '_index' => 'olive__youtube-comments',
        '_type' => 'comment',
        '_id' => 'Ugz7SCFcJDr4KLqyrSt4AaABAg',
      ),
    ),
    53 => 
    array (
      'id' => 'Ugz7SCFcJDr4KLqyrSt4AaABAg',
      'text' => 'Oooh Miranda Sings is hereeee',
      'video_id' => 'gl1aHhXnN1k',
      'channel' => 
      array (
        'id' => 'UCNftHHr4eISGrdaMEZSZ_vA',
        'title' => 'Jiminie Pabo',
      ),
      'created_at' => '2018-12-05 03:13:22',
      'called_at' => '2018-12-05 03:15:21',
      'sentiment' => 
      array (
        'neu' => 0.334000000000000019095836023552692495286464691162109375,
        'pos' => 0.333000000000000018207657603852567262947559356689453125,
        'neg' => 0.333000000000000018207657603852567262947559356689453125,
      ),
    ),
    54 => 
    array (
      'create' => 
      array (
        '_index' => 'olive__youtube-comments',
        '_type' => 'comment',
        '_id' => 'Ugzy_7VmsqJI3OSEnyR4AaABAg',
      ),
    ),
    55 => 
    array (
      'id' => 'Ugzy_7VmsqJI3OSEnyR4AaABAg',
      'text' => 'Can we talk about that victorious throwback',
      'video_id' => 'gl1aHhXnN1k',
      'channel' => 
      array (
        'id' => 'UCJ78xy6rtSmNzb3OXMZFq6A',
        'title' => 'Goodbye',
      ),
      'created_at' => '2018-12-05 03:13:22',
      'called_at' => '2018-12-05 03:15:21',
      'sentiment' => 
      array (
        'pos' => 0.5,
        'neu' => 0.25100000000000000088817841970012523233890533447265625,
        'neg' => 0.25,
      ),
    ),
    56 => 
    array (
      'create' => 
      array (
        '_index' => 'olive__youtube-comments',
        '_type' => 'comment',
        '_id' => 'UgxyhIMo2EgqU0r0EQ94AaABAg',
      ),
    ),
    57 => 
    array (
      'create' => 
      array (
        '_index' => 'olive__youtube-comments',
        '_type' => 'comment',
        '_id' => 'Ugxqoq9sKRNaa_xCIRl4AaABAg',
      ),
    ),
    58 => 
    array (
      'id' => 'Ugxqoq9sKRNaa_xCIRl4AaABAg',
      'text' => 'Selam arkadaşlar bu videoya 24 saatte 4000 like gelir mi SİZ HARİKASINIZ Beni Instagram\'dan takip etmeyi unutmayın. İyi seyirler https: www.instagram.com melihtasci0',
      'video_id' => 'A6Nnfi1Cijs',
      'channel' => 
      array (
        'id' => 'UCxNbsO-9EQ7D_wd5_qZ9FJw',
        'title' => 'Melih Taşçı',
      ),
      'created_at' => '2018-12-04 18:35:49',
      'called_at' => '2018-12-05 03:15:22',
      'sentiment' => 
      array (
        'pos' => 0.5,
        'neu' => 0.25100000000000000088817841970012523233890533447265625,
        'neg' => 0.25,
      ),
    ),
  ),
);

print_r(Document::bulkInsert($json));

    }
}
