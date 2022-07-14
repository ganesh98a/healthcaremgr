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
    | Like any Laravel application, you can configure and use different
    | database connections. Although you are most likely to only use
    | a single database connection for Nomad, the choice is there.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('OCS_DB_NAME', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('OCS_DB_HOST_NAME', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('OCS_DB_NAME', 'hcm_dev'),
            'username' => env('OCS_DB_USER_NAME', 'root'),
            'password' => trim(env('OCS_DB_PASSWORD', '')),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('OCS_DB_HOST_NAME', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('OCS_DB_NAME', 'forge'),
            'username' => env('OCS_DB_USER_NAME', 'forge'),
            'password' => env('OCS_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('OCS_DB_HOST_NAME', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('OCS_DB_NAME', 'forge'),
            'username' => env('OCS_DB_USER_NAME', 'forge'),
            'password' => env('OCS_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],

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

];
