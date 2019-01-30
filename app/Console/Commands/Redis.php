<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis as RedisCache;

use App\Elasticsearch\Document;

use Carbon\Carbon;

class Redis extends Command
{
    public $alias;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:store {--part=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Yoğun içerikleri redis\'e al.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->alias = str_slug(config('app.name'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $part = $this->option('part');

        if (!$part)
        {
            $part = $this->choice('Bir parça seçin:', [
                '__all',
                'total_document',
            ], 0);
        }

        switch ($part)
        {
            case '__all':
                $this->total_document();
                break;
            case 'total_document': $this->total_document(); break;
        }
    }

    /**
     * Toplam Döküman Sayısı
     *
     * @return mixed
     */
    public function total_document()
    {
        $data = [];

        # [ total tweets ] #
        $tweets = @Document::count([ 'twitter', 'tweets', '*' ], 'tweet')->data['count'];
        # [ total entries ] #
        $entries = @Document::count([ 'sozluk', '*' ], 'entry')->data['count'];
        # [ total articles ] #
        $articles = @Document::count([ 'media', 's*' ], 'article')->data['count'];
        # [ total comments ] #
        $comments = @Document::count([ 'youtube', 'comments', '*' ], 'comment')->data['count'];
        # [ total videos ] #
        $videos = @Document::count([ 'youtube', 'videos' ], 'video')->data['count'];
        # [ total searches ] #
        $searches = @Document::count([ 'google', 'search' ], 'search')->data['count'];
        # [ total products ] #
        $products = @Document::count([ 'shopping', '*' ], 'product')->data['count'];

        if ($tweets) $data['tweets'] = $tweets;
        if ($entries) $data['entries'] = $entries;
        if ($articles) $data['articles'] = $articles;
        if ($videos) $data['videos'] = $videos;
        if ($searches) $data['searches'] = $searches;
        if ($products) $data['products'] = $products;

        RedisCache::set(implode(':', [ $this->alias, 'documents', 'total' ]), json_encode($data));

        $this->info(json_encode($data, JSON_PRETTY_PRINT));
    }
}
