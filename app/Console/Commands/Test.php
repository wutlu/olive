<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Models\Proxy;
use App\Models\Twitter\StreamingUsers;
use App\Models\Twitter\BlockedTrendKeywords as TwitterBlockedTrendKeywords;

use App\Wrawler;

use App\Instagram;

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
        $instagram = new Instagram;
        $connect = $instagram->connect('https://www.instagram.com/explore/locations/494302844/istanbul-province/');
        $data = $instagram->data('location');

        print_r($data);
    }
}
