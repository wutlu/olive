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
    public $classes = [ 'pos', 'neg', 'neu', 'hat', 'bet', 'nud', 'que' ]; //, 'ign', 'prefix'

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
