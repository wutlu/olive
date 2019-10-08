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

        $category = new Sentiment;
        $category->engine('category');
        $text = '';
            $category_name = $category->net($text ? $text : '', 'category');

            print_r($category_name);
    }
}
