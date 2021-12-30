<?php declare(strict_types=1);

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
];
