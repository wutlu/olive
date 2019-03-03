<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Elasticsearch\ClientBuilder;

use App\Elasticsearch\Indices;

use App\Utilities\Term;

use App\Jobs\Elasticsearch\CreateTrendIndexJob;

use App\Models\Option;

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
                'index_list' => 'List Index',
                'index_create' => 'Create Index',
                'index_update' => 'Update Index',
                'index_delete' => 'Delete Index',
                'doc_delete' => 'Delete Document',
            ],
            'index_list'
        );

        if ($option == 'index_create')
        {
            $module = $this->choice(
                'Enter a module:',
                [
                    'trend.indices' => 'Trend Indices',
                ]
            );

            switch ($module)
            {
                case 'trend.indices':
                    $opt = Option::where('key', 'trend.index')->first();

                    if (@$opt->value == 'on')
                    {
                        $this->error('Index already created.');
                    }
                    else
                    {
                        CreateTrendIndexJob::dispatch()->onQueue('elasticsearch');

                        $this->info('Request to create index sent.');
                    }
                break;
            }
        }
        else
        {
            $alias = config('system.db.alias');

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
                        'count' => number_format(@$stats[$no]['primaries']['docs']['count']),
                        'size' => @$stats[$no]['primaries']['store']['size_in_bytes']
                    ];

                    $autocomlete[] = $name;
                }

                $this->table(['Key', 'Docs Count', 'Size'], $indices);

                if ($option == 'index_delete')
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
                else if ($option == 'doc_delete')
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
                else if ($option == 'index_update')
                {
                    $index = $this->anticipate('Select index', $autocomlete);

                    if ($index)
                    {
                        $index = $alias.'__'.$index;

                        try
                        {
                            $response = $client->indices()->getSettings([ 'index' => $index ]);

                            foreach ($response as $item)
                            {
                                $this->line($item['settings']['index']['provided_name']);

                                $settings = [
                                    'number_of_shards' => $item['settings']['index']['number_of_shards'],
                                ];

                                $this->line(json_encode($settings, JSON_PRETTY_PRINT));

                                $settings = [
                                    'total_fields_limit' => @$item['settings']['index']['mapping']['total_fields']['limit'],
                                    'number_of_replicas' => @$item['settings']['index']['number_of_replicas'],
                                    'read_only_allow_delete' => @$item['settings']['index']['blocks']['read_only_allow_delete'],
                                    'refresh_interval' => @$item['settings']['index']['refresh_interval'],
                                    'index_mapper_dynamic' => @$item['settings']['index.mapper.dynamic'],
                                ];

                                $this->info(json_encode($settings, JSON_PRETTY_PRINT));
                            }

                        }
                        catch (\Exception $e)
                        {
                            $this->error('Index not found!');

                            die();
                        }

                        $settings = [];

                        $total_fields_limit = $this->ask('total_fields_limit: (integer)');
                        if ($total_fields_limit) $settings['index']['mapping']['total_fields']['limit'] = $total_fields_limit;

                        $number_of_replicas = $this->ask('number_of_replicas: (integer)');
                        if ($number_of_replicas) $settings['number_of_replicas'] = $number_of_replicas;

                        $read_only_allow_delete = $this->ask('read_only_allow_delete: (boolean)');
                        if ($read_only_allow_delete) $settings['index']['blocks']['read_only_allow_delete'] = $read_only_allow_delete == 'null' ? null : $read_only_allow_delete;

                        $refresh_interval = $this->ask('refresh_interval: (string bkz: 5s)');
                        if ($refresh_interval) $settings['refresh_interval'] = $refresh_interval;

                        $this->info(json_encode($settings, JSON_PRETTY_PRINT));

                        if ($this->confirm('Do you approve information?'))
                        {
                            try
                            {
                                $response = $client->indices()->putSettings([
                                    'index' => $index,
                                    'body' => [
                                        'settings' => $settings
                                    ]
                                ]);

                                $this->info('Changes have been updated.');
                            }
                            catch (\Exception $e)
                            {
                                $this->error(json_encode(json_decode($e->getMessage()), JSON_PRETTY_PRINT));
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
        }

        if (!$option)
        {
            $this->error('You have not taken any action.');
        }
    }
}
