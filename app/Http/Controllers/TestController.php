<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Elasticsearch\Document;

class TestController extends Controller
{
    public static function test()
    {
    	$items = [];

        $query = @Document::list([ 'media', '*' ], 'article', [
		    'size' => 0,
		    'query' => [
		        'bool' => [
		        	'must' => [
		        		[
		        			[ 'match' => [ 'status' => 'ok' ] ]
		        		]
		        	],
		            'filter' => [
		                [
		                    'range' => [ 'created_at' => [ 'gte' => 'now-1h' ] ]
		                ]
		            ]
		        ]
		    ],
		    'aggs' => [
		    	'hit_keywords' => [
		    		'significant_terms' => [
		    			'field' => 'title',
		    			'size' => 50,
		    			'min_doc_count' => 10
		    		]
		    	]
		    ]
		])->data['aggregations']['hit_keywords']['buckets'];

		if ($query)
		{
			foreach ($query as $row)
			{
		        $item = @Document::list([ 'media', '*' ], 'article', [
		        	'size' => 1,
		        	'query' => [
		        		'bool' => [
		        			'must' => [
		        				[
			                        'more_like_this' => [
			                            'fields' => [ 'title' ],
			                            'like' => $row['key'],
			                            'min_term_freq' => 1,
			                            'min_doc_freq' => 1
			                        ]
		                        ],
			        			[
			        				'match' => [ 'status' => 'ok' ]
			        			]
			        		],
			        		'filter' => [
			        			[
				                    'range' => [ 'created_at' => [ 'gte' => 'now-1h' ] ]
				                ]
			        		]
		        		]
		        	],
		        	'_source' => [ 'title' ]
		        ])->data['hits']['hits'][0]['_source']['title'];

		        $items[md5($item)] = $item;
			}
		}

		print_r($items);

        return view('test');
    }
}
