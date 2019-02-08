<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Utilities\DateUtility;
use App\Utilities\Crawler;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Wrawler;

use Carbon\Carbon;

use App\Models\Crawlers\MediaCrawler;
use App\Elasticsearch\Document;
use App\Elasticsearch\Indices;

use App\Models\Crawlers\Host;

use App\Models\Proxy;

class TestController extends Controller
{
    public static function test()
    {
        $documents = Document::listByMultiQuery(
            [
                [
                    'index' => Indices::name([ 'twitter', 'tweets', '*' ]),
                    'query' => [
                        'size' => 1000,
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'query_string' => [ 'default_field' => 'text', 'query' => 'merhaba' ] ]
                                ],
                                'filter' => [
                                    [
                                        'range' => [
                                            'created_at' => [
                                                'format' => 'YYYY-MM-dd',
                                                'gte' => date('Y-m-d', strtotime('-10 day')),
                                                'lte' => date('Y-m-d')
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'sort' => [ 'created_at' => 'DESC' ],
                        '_source' => [ 'user.name', 'user.screen_name', 'text', 'created_at', 'sentiment' ]
                    ],
                    'hitsPerPage' => 5
                ]
            ]
        );

        echo "<pre>";
        print_r($documents);
    }
}
