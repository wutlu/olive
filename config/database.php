<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => env('DB_SCHEMA', 'public'),
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],

        'elasticsearch' => [
            'node' => [
                'ips' => explode('|', env('ELASTICSEARCH_NODE_IPS', '127.0.0.1:9200')),
                'names' => explode('|', env('ELASTICSEARCH_NODE_NAMES', 'node-1'))
            ]
        ]

    ],

    'elasticsearch' => [
        'google' => [
            'search' => [
                'settings' => [
                    'total_fields_limit' => 30,
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                    'refresh_interval' => '10s'
                ]
            ]
        ],
        'media' => [
            'groups' => [
                's01',
                's02',
                's03',
                's04',
                's05',
                's06',
                's07',
                's08',
                's09',
                's10',
                's11',
                's12',
                's13',
                's14',
                's15',
                's16',
                's17',
                's18',
                's19',
                's20',
            ],
            'article' => [
                'settings' => [
                    'total_fields_limit' => 30,
                    'number_of_shards' => 4,
                    'number_of_replicas' => 0,
                    'refresh_interval' => '5s'
                ]
            ]
        ],
        'shopping' => [
            'product' => [
                'settings' => [
                    'total_fields_limit' => 40,
                    'number_of_shards' => 2,
                    'number_of_replicas' => 0,
                    'refresh_interval' => '5s'
                ]
            ]
        ],
        'sozluk' => [
            'entry' => [
                'settings' => [
                    'total_fields_limit' => 30,
                    'number_of_shards' => 2,
                    'number_of_replicas' => 0,
                    'refresh_interval' => '10s'
                ]
            ]
        ],
        'twitter' => [
            'tweet' => [
                'settings' => [
                    'total_fields_limit' => 500,
                    'number_of_shards' => 4,
                    'number_of_replicas' => 1,
                    'refresh_interval' => '30s'
                ]
            ]
        ],
        'youtube' => [
            'video' => [
                'settings' => [
                    'total_fields_limit' => 50,
                    'number_of_shards' => 2,
                    'number_of_replicas' => 0,
                    'refresh_interval' => '10s'
                ]
            ],
            'comment' => [
                'settings' => [
                    'total_fields_limit' => 50,
                    'number_of_shards' => 2,
                    'number_of_replicas' => 0,
                    'refresh_interval' => '30s'
                ]
            ]
        ],

        'trend' => [
            'title' => [
                'settings' => [
                    'total_fields_limit' => 30,
                    'number_of_shards' => 2,
                    'number_of_replicas' => 0,
                    'refresh_interval' => '5s'
                ]
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
