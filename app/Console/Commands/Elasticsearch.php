<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Elasticsearch\ClientBuilder;
use App\Elasticsearch\Indices;
use App\Utilities\Term;

class Elasticsearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elasticsearch index işlemleri.';

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
        $option = $this->choice(
            'What do you want to do?',
            [
                'list_index' => 'List of Indices',
                'delete_index' => 'Delete Indices',
                'delete_doc' => 'Delete Document',
            ],
            'list_index'
        );

        $alias = str_slug(config('app.name'));

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
            'retries' => 5
        ]);

        $response = $client->indices()->stats([ 'index' => $alias.'__'.'*' ]);

        if (@$response['indices'])
        {
            [$indices, $stats] = array_divide($response['indices']);

            $autocomlete = [];

            foreach ($indices as $no => $key)
            {
                unset($indices[$no]);

                $name = str_replace([ $alias, '__' ], '', $key);

                $indices[] = [
                    'key' => $name,
                    'count' => number_format(@$stats[$no]['total']['docs']['count']),
                    'size' => @$stats[$no]['total']['store']['size_in_bytes']
                ];

                $autocomlete[] = $name;
            }

            $this->table(['Key', 'Docs Count', 'Size'], $indices);

            if ($option == 'delete_index')
            {
                $name = $this->anticipate('Please select the index you want to delete', $autocomlete);

                if ($name)
                {
                    if ($this->confirm('Are you sure?'))
                    {
                        $password = $this->secret('Password?');

                        if ($password == config('app.password'))
                        {
                            $this->line(Indices::drop([ $name ]));
                            $this->info('Index deleted.');
                        }
                        else
                        {
                            $this->error('The password you entered is not valid.');
                        }
                    }
                    else
                    {
                        $this->line('You stopped deleting the index.');
                    }
                }
                else
                {
                    $this->error('Please specify an index!');
                }
            }
            else if ($option == 'delete_doc')
            {
                $index = $this->anticipate('Select index', $autocomlete);

                if ($index)
                {
                    $index = $alias.'__'.$index;

                    try
                    {
                        $response = $client->indices()->getMapping([ 'index' => $index ]);
                    }
                    catch (\Exception $e)
                    {
                        $this->error('Index not found!');

                        die();
                    }

                    [$keys] = array_divide($response[$index]['mappings']);

                    foreach ($keys as $no => $key)
                    {
                        unset($keys[$no]);

                        $keys[$key] = [
                            'key' => $key
                        ];
                    }

                    $this->table(['Key'], $keys);

                    $type = $this->choice('Type', array_keys($keys));

                    if ($type)
                    {
                        $id = $this->ask('Id');

                        if ($id)
                        {
                            try
                            {
                                $response = $client->delete([
                                    'index' => $index,
                                    'type' => $type,
                                    'id' => $id
                                ]);

                                $this->info('Index deleted!');
                            }
                            catch (\Exception $e)
                            {
                                $this->error('Document not found!');
                            }
                        }
                    }
                }
                else
                {
                    $this->error('Please specify an index!');
                }
            }
        }
        else
        {
            $this->line('Indices not found.');
        }

        if (!$option)
        {
            $this->error('You have not taken any action.');
        }
    }
}
