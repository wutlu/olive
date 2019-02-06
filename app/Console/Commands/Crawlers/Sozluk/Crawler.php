<?php

namespace App\Console\Commands\Crawlers\Sozluk;

use Illuminate\Console\Command;

use App\Elasticsearch\Indices;
use App\Elasticsearch\Document;

use Elasticsearch\ClientBuilder;

use App\Models\Crawlers\SozlukCrawler;

use App\Utilities\Crawler as CrawlerUtility;

use App\Jobs\Elasticsearch\BulkInsertJob;

use Sentiment;
use System;
use Mail;

use App\Mail\ServerAlertMail;

class Crawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sozluk:crawler {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sözlük girdilerini topla.';

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
        $id = $this->option('id');

        $id = $id ? $id : $this->ask('Enter a sözlük id');

        $sozluk = SozlukCrawler::where('id', $id)->where('status', true)->first();

        if (@$sozluk)
        {
            $timeStart = time()-20;

            $entry_id = $sozluk->last_id;
            $max_attempt = $sozluk->max_attempt;
            $errors = [];
            $chunk = [ 'body' => [] ];
            $chunk_count = 0;
            $stream = true;
            $tmp_error = 0;
            $deep_try = 1;
            $last_entry_id = $entry_id;

            while ($stream)
            {
                $save = false;
                $max_try = $deep_try*$max_attempt;
                $timeNow = time();
                $second = $timeNow - $timeStart;

                if ($second >= 10)
                {
                    SozlukCrawler::where('id', $id)->update([
                        'pid' => getmypid(),
                        'status' => true
                    ]);

                    $timeStart = time();

                    $this->line('pid update [second = '.$second.']');
                }

                $item = CrawlerUtility::entryDetection(
                    $sozluk->site,
                    $sozluk->url_pattern,
                    $entry_id,
                    $sozluk->selector_title,
                    $sozluk->selector_entry,
                    $sozluk->selector_author,
                    $sozluk->proxy
                );

                if ($item->status == 'ok')
                {
                    if ($entry_id >= $last_entry_id)
                    {
                        $chunk['body'][] = [
                            'create' => [
                                '_index' => Indices::name([ 'sozluk', $sozluk->id ]),
                                '_type' => 'entry',
                                '_id' => $entry_id
                            ]
                        ];

                        $sentiment = new Sentiment;

                        $chunk['body'][] = [
                            'id' => $entry_id,

                            'url' => $item->page,
                            'group_name' => $item->group_name,

                            'title' => $item->data['title'],
                            'entry' => $item->data['entry'],
                            'author' => $item->data['author'],

                            'created_at' => $item->data['created_at'],
                            'called_at' => date('Y-m-d H:i:s'),

                            'site_id' => $sozluk->id,

                            'sentiment' => $sentiment->score($item->data['entry'])
                        ];

                        $chunk_count++;

                        $tmp_error = 0;
                        $deep_try = 1;

                        $errors = [];

                        $last_entry_id++;
                    }

                    $this->info('['.$entry_id.']');

                    if ($chunk_count >= $sozluk->chunk)
                    {
                        $sozluk->last_id = $entry_id;

                        $save = true;

                        BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');

                        $chunk = [ 'body' => [] ];

                        $chunk_count = 0;

                        $this->info('chunk saved');
                    }
                }
                else
                {
                    $tmp_error++;

                    $this->error('['.$entry_id.']');
                }

                $entry_id++;

                $this->line('error: ['.$tmp_error.'/'.$max_try.' deep: '.$deep_try.'] chunk: ['.$chunk_count.']');

                if ($tmp_error >= $max_try)
                {
                    $errors[] = $item->error_reasons;

                    if ($deep_try < $sozluk->deep_try)
                    {
                        $entry_id = $sozluk->last_id;
                        $deep_try++;
                        $tmp_error = 0;

                        $this->line('waiting');

                        sleep(10);
                    }
                    else
                    {
                        $sozluk->status = false;
                        $sozluk->test = false;
                        $sozluk->pid = null;
                        $sozluk->off_reason = json_encode($errors);

                        $save = true;

                        System::log(
                            $sozluk->off_reason,
                            'App\Console\Commands\Crawlers\Sozluk\Crawler::handle(int '.$sozluk->id.')',
                            10
                        );

                        Mail::queue(new ServerAlertMail($sozluk->name.' Sözlük Botu [DURDU]', $sozluk->off_reason));

                        $stream = false;
                    }
                }

                if ($save)
                {
                    $sozluk->save();
                }
            }
        }
        else
        {
            $this->error('Eylemin çalışması için botu aktif edin.');
        }
    }
}
