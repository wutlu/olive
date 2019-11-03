<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Elasticsearch\Document;

class ReplicaController extends Controller
{
    public function __construct()
    {
        ### [ üyelik zorunlu ] ###
        $this->middleware([ 'auth' ]);

        ### [ 100 işlemden sonra 5 dakika ile sınırla ] ###
        $this->middleware('throttle:100,5')->only([
        ]);
    }

    /**
     * Replica Tespiti
     *
     * @return view
     */
    public static function dashboard()
    {
    	$elements = [
    		'start_date',
    		'end_date',
    	];

        $elements = implode(',', $elements);

        return view('replica', compact('elements'));
    }

    /**
     * Replica Tespiti Sorgu
     *
     * @return array
     */
    public static function search(Request $request)
    {
        $q = [
            'from' => $request->skip,
            'size' => $request->take,
            'sort' => [ 'created_at' => 'desc' ],
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'created_at' => [
                                    'format' => 'YYYY-MM-dd',
                                    'gte' => $request->start_date,
                                    'lte' => $request->end_date
                                ]
                            ]
                        ]
                    ],
                    'must' => [
                        [ 'exists' => [ 'field' => 'created_at' ] ],
                        [
                            'more_like_this' => [
                                'fields' => [ 'title', 'description' ],
                                'like' => 'bilgi',
                                'min_term_freq' => 1,
                                'min_doc_freq' => 1
                            ]
                        ]
                    ]
                ]
            ],
            'min_score' => 1,
            'from' => $request->skip,
            'size' => $request->take,
        ];

        $query = Document::search([ 'media', '*' ], 'article', $q);

        $results = [
        	'status' => $query->status
        ];

        if ($query->status == 'ok')
        {
        	$results['hits'] = $query->data['hits']['hits'];
        	$results['stats'] = [
        		'total' => $query->data['hits']['total']
        	];
        }

        return $results;
    }
}
