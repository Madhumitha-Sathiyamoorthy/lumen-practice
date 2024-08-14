<?php
return [
    'default' => 'mongodb',
    'connections' => [
        'mongodb' => [
            'driver' => 'mongodb',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 27017),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'options' => [
                'database' => 'admin'
            ]
        ],
    ],
    'migrations' => 'migrations',
    
    'redis' => [
        'client' => env('REDIS_CLIENT', 'predis'),
        'cluster' => env('REDIS_CLUSTER', false),

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DATABASE', 0),
            'password' => env('REDIS_PASSWORD', null),
        ]

    ],
    
    // 'default' => env('DB_CONNECTION', 'mysql'),
    // 'connections' => [
    //         'mysql' => [
    //             'driver' => 'mysql',
    //             'host' => env('DB_HOST', '127.0.0.1'),
    //             'port' => env('DB_PORT', '3306'),
    //             'database' => env('DB_DATABASE'),
    //             'username' => env('DB_USERNAME'),
    //             'password' => env('DB_PASSWORD'),
    //             'unix_socket' => env('DB_SOCKET'),
    //             'charset' => 'utf8mb4',
    //             'collation' => 'utf8mb4_unicode_ci',
    //             'prefix' => '',
    //             'strict' => true,
    //             'engine' => null,
    //         ],
    //     ],
];

