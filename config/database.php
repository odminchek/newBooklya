<?php

return [
    'fetch' => PDO::FETCH_CLASS,
    'default' => env('DB_CONNECTION', 'mongodb'),
    'connections' => [
        'mongodb' => [
            'driver'   => 'mongodb',
            'host'     => env('DB_HOST', 'localhost'),
            'port'     => env('DB_PORT', 27017),
            'database' => env('DB_DATABASE', 'booklya'),
            'username' => env('DB_USERNAME', 'booklya'),
            'password' => env('DB_PASSWORD', 'booklya'),
            'options' => [
                'database' => 'booklya' // sets the authentication database required by mongo 3
            ]
        ],
    ],
    'migrations' => 'migrations',
    'redis' => [
        'cluster' => false,
        'default' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],
    ],
];
