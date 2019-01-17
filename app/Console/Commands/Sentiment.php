<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Sentiment as SentimentLib;

use App\Utilities\Term;

class Sentiment extends Command
{
    /**
     * Duyru Türleri
     *
     * @var array
     */
    public $classes = [ 'pos', 'neg', 'neu' ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sentiment:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Duygu analizi testi.';

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
        $keys = [
            'neg' => 'Negatif',
            'pos' => 'Pozitif',
            'neu' => 'Nötr'
        ];

        $strings = [
            'Bugün çok neşeliyim, beğeni neşemi kimse bozamaz net! İyi bir hayat.',
            'Artık sevmeyeceğim.',
            'Berbat bir dünyada yaşıyoruz.',
            'Neden böyle oldu ki?'
        ];

        $sentiment = new SentimentLib;

        foreach ($strings as $string)
        {
            $scores = $sentiment->score($string);
            $class = $sentiment->categorise($string);

            $this->line('');

            $this->info($string);

            switch ($class)
            {
                case 'neg': $this->error($keys[$class]); break;
                case 'neu': $this->line($keys[$class]); break;
                case 'pos': $this->info($keys[$class]); break;
            }

            print_r($scores);
        }
    }

    /**
     * Duygu Analizi veritabanı güncelle.
     *
     * @return mixed
     */
    public static function update()
    {
        $dictionaryFolder = database_path('analysis/dictionaries/');

        $sentiment = new SentimentLib;
        $sentiment->reloadDictionaries($dictionaryFolder);

        echo Term::line('Duygu analizi veritabanı güncellendi.');
    }
}
