<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Sentiment extends Command
{
    public $classes = [ 'pos', 'neg', 'neu' ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sentiment:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Duygu analizi listesini günceller.';

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
}
