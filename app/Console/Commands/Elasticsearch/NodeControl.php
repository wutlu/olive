<?php

namespace App\Console\Commands\Elasticsearch;

use Illuminate\Console\Command;

use System;
use Mail;
use App\Mail\ServerAlertMail;

use Elasticsearch\ClientBuilder;

class NodeControl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:node_control';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Node sayısı örtüşmüyorsa e-posta ile yöneticilere bildir.';

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
        $ips = config('database.connections.elasticsearch.node.ips');
        $names = config('database.connections.elasticsearch.node.names');

        $nodes = array_combine($names, $ips);

        $client = ClientBuilder::fromConfig([
            'hosts' => $ips,
            'retries' => 5
        ]);

        try
        {
            $es = $client->cat()->nodes([
                'client' => [
                    'timeout' => 1,
                    'connect_timeout' => 1
                ]
            ]);

            $lines[] = 'Çalışmayan node veya nodelar mevcut.';
            $lines[] = '';
            $lines[] = 'Lütfen sisteme müdahale edin!';

            $lines[] = '';

            foreach ($nodes as $key => $node)
            {
                $have = is_integer(array_search($key, array_column($es, 'name')));

                if ($have)
                {
                    $lines[] = '- [✔] '.$key.' - '.$node;
                    $this->info($node);
                }
                else
                {
                    $lines[] = '- [✕] '.$key.' - '.$node;
                    $this->error($node);
                }
            }

            $body = implode(PHP_EOL, $lines);

            if (count($ips) != count($es) && config('app.env') == 'production')
            {
                Mail::queue(
                    new ServerAlertMail(
                        'ELASTICSEARCH PROBLEM! ['.count($ips).'/'.count($es).']',
                        $body
                    )
                );
            }
        }
        catch (\Exception $e)
        {
            System::log(
                $e->getMessage(),
                'App\Console\Commands\Elasticsearch\NodeControl::handle()',
                10
            );
        }
    }
}
