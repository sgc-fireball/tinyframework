<?php

declare(strict_types=1);

return [
    'default' => env('DATABASE_DRIVER', 'mysql'),
    'mysql' => [
        'driver' => \TinyFramework\Database\MySQL\Database::class,
        'host' => env('MYSQL_HOST', 'localhost'),
        'port' => env('MYSQL_PORT', 3306),
        'username' => env('MYSQL_USERNAME', 'root'),
        'password' => env('MYSQL_PASSWORD', ''),
        'database' => env('MYSQL_DATABASE', 'tinyframework'),
        'charset' => env('MYSQL_CHARSET', 'utf8mb4'),
        'collation' => env('MYSQL_COLLATION', 'utf8mb4_general_ci'),
        'timezone' => env('MYSQL_TIMEZONE', env('APP_TIMEZONE', 'UTC')),
    ],
    'sqlite3' => [
        'driver' => \TinyFramework\Database\SQLite\Database::class,
        'file' => env('SQLITE3_FILE', storage_dir('database.sqlite3')),
        'flags' => env('SQLITE3_FLAGS', 2 /* SQLITE3_OPEN_READWRITE */ | 4 /* SQLITE3_OPEN_CREATE*/),
        'encryption' => env('SQLITE3_ENCRYPTION'),
        'timezone' => env('SQLITE3_TIMEZONE', env('APP_TIMEZONE', 'UTC')),
    ],
];
