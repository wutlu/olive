<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Borsa;
use App\Models\BorsaQuery;

use App\Elasticsearch\Document;

class BorsaCounter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'borsa:counter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'İçerik sayıları belirlenmeyen hisselerin içerik sayılarını belirler.';

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
        $borsa = Borsa::where('date', date('Y-m-d'))->get();

        if (count($borsa))
        {
            foreach ($borsa as $hisse)
            {
                $this->info('Hisse: '.$hisse->name);

                $query = BorsaQuery::where('name', $hisse->name)->first();

                if (@$query)
                {
                    /*!
                     * pozitif query
                     */
                    if ($query->query_pos)
                    {
                        $es_query = Document::count(
                            '*',
                            'entry,article,document,tweet,product,video,comment,media',
                            [
                                'query' => [
                                    'bool' => [
                                        'filter' => [
                                            'range' => [ 'created_at' => [ 'format' => 'YYYY-MM-dd', 'gte' => date('Y-m-d') ] ]
                                        ],
                                        'must' => [
                                            [ 'exists' => [ 'field' => 'created_at' ] ],
                                            [
                                                'query_string' => [
                                                    'fields' => [
                                                        'title',
                                                        'description',
                                                        'entry',
                                                        'text'
                                                    ],
                                                    'query' => $query->query_pos,
                                                    'default_operator' => 'AND'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        );

                        if ($es_query->status == 'ok')
                        {
                            $hisse->total_pos = $es_query->data['count'];
                            $hisse->update();

                            $this->info('Pozitif değer güncellendi: '.$es_query->data['count']);
                        }
                        else
                        {
                            $this->error('ES bağlantısı kurulamadı.');
                        }
                    }
                    else
                    {
                        $this->error('Pozitif sorgu yok!');
                    }

                    /*!
                     * negatif query
                     */
                    if ($query->query_neg)
                    {
                        $es_query = Document::count(
                            '*',
                            'entry,article,document,tweet,product,video,comment,media',
                            [
                                'query' => [
                                    'bool' => [
                                        'filter' => [
                                            'range' => [ 'created_at' => [ 'format' => 'YYYY-MM-dd', 'gte' => date('Y-m-d') ] ]
                                        ],
                                        'must' => [
                                            [ 'exists' => [ 'field' => 'created_at' ] ],
                                            [
                                                'query_string' => [
                                                    'fields' => [
                                                        'title',
                                                        'description',
                                                        'entry',
                                                        'text'
                                                    ],
                                                    'query' => $query->query_neg,
                                                    'default_operator' => 'AND'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        );

                        if ($es_query->status == 'ok')
                        {
                            $hisse->total_neg = $es_query->data['count'];
                            $hisse->update();

                            $this->info('Negatif değer güncellendi: '.$es_query->data['count']);
                        }
                        else
                        {
                            $this->error('ES bağlantısı kurulamadı.');
                        }
                    }
                    else
                    {
                        $this->error('Negatif sorgu yok!');
                    }
                }
                else
                {
                    $this->error('Sorgu satırı bulunamadı!');
                }

                if ($hisse->total_pos !== null && $hisse->total_neg !== null)
                {
                    $hisse->pos_neg = $hisse->total_pos - $hisse->total_neg;
                    $hisse->update();
                }
            }
        }
    }
}
