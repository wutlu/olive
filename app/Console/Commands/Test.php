<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Sentiment;

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
    protected $description = 'Test komutu.';

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
        $sentiment = new Sentiment;
        $sentiment->engine('category');

        print_r($sentiment->net('Günaydın #Galatasaray Ailesi https://t.co/nnoKmbpraf bilim ile çalışmalı bence.'));
    }
}
