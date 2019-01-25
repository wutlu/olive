<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Elasticsearch\Document;

use App\Models\Crawlers\SozlukCrawler;
use App\Models\Crawlers\MediaCrawler;

use Carbon\Carbon;

class ContentController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
        $this->middleware('auth');

        /**
         ***** ZORUNLU *****
         *
         * - Organizasyonu Olanlar
         */
        $this->middleware('organisation:have');

        /**
         ***** ZORUNLU *****
         *
         * - Organizasyon Onayı
         */
        $this->middleware('can:organisation-status');
    }

    /**
     * Modül Ayraç
     *
     * @return view
     */
    public static function module(string $es_index, string $es_type, string $es_id)
    {
        $document = Document::get($es_index, $es_type, $es_id);

        if ($document->status == 'ok')
        {
            $document = $document->data;
            $id_segment = explode('-', $es_index);

            switch ($es_type)
            {
                case 'entry':
                    $crawler = SozlukCrawler::where('id', $id_segment[1])->firstOrFail();

                    $title = implode(' ', [ $crawler->name, 'Entry', '#'.$es_id ]);
                break;

                case 'product':
                    $crawler = ShoppingCrawler::where('id', $id_segment[1])->firstOrFail();

                    $title = implode(' ', [ $crawler->name, 'Entry', '#'.$es_id ]);
                break;

                case 'tweet':
                    $title = implode(' ', [ 'Twitter', 'Tweet', '#'.$es_id ]);
                break;

                case 'video':
                    $title = implode(' ', [ 'YouTube', 'Video', '#'.$es_id ]);
                break;

                case 'comment':
                    $title = implode(' ', [ 'Youtube', 'Comment', '#'.$es_id ]);
                break;

                default: abort(404); break;
            }

            return view(implode('.', [ 'content', $es_type ]), compact('document', 'title'));
        }

        return abort(404);
    }

    /**
     * Histogram
     *
     * @return array
     */
    public static function histogram(string $es_index, string $es_type)
    {
        $document = Document::list($es_index, $es_type, [
                'size' => 0,
                'aggs' => [
                    'dayOfWeek' => [
                        'terms' => [
                            'script' => [
                                'lang' => 'painless',
                                'source' => 'doc[\'created_at\'].value.dayOfWeekEnum.value'
                            ]
                        ]
                    ]
                ]
        ]);

            print_r($document);
            return '';

        return [
            'status' => 'err'
        ];
    }
}
