<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Elasticsearch\Document;

use Carbon\Carbon;

class TrendController extends Controller
{
    public function __construct()
    {
        $this->middleware([ 'auth', 'organisation:have' ]);
    }

    # 
    # trend analiz ekranı
    # 
    public function dashboard()
    {
        $time = date('Y-m-d', strtotime('2018-11-15'));
        //$time = Carbon::now()->format('Y-m-d');

        $query = Document::list(
            [
                'twitter',
                'tweets',
                '*'
            ],
            'tweet',
            [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'range' => [
                                'created_at' => [
                                    'format' => 'YYYY-MM-dd',
                                    'gte' => $time
                                ]
                            ]
                        ]
                    ]
                ],
                'aggs' => [
                    'top_terms' => [
                        'significant_text' => [
                            'field' => 'text',
                            'size' => 1000
                        ]
                    ]
                ],
                'size' => 0
            ]
        );

        print_r($query);

        exit();

        return view('trends.live');
    }

    # 
    # trend endeks ekranı
    # 
    public function index()
    {
        return view('trends.index');
    }

    # 
    # trend arşiv ekranı
    # 
    public function archive()
    {
        return view('trends.archive');
    }
}
