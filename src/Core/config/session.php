<?php

declare(strict_types=1);

return [
    'cookie' => env('SESSION_COOKIE', 'session'),
    'ttl' => env('SESSION_TTL', 24 * 60 * 60),

    'default' => env('SESSION_DRIVER', 'redis'),
    'file' => [
        'driver' => TinyFramework\Session\FileSession::class,
        'path' => env('SESSION_FILE_PATH', 'storage/sessions')
    ],
    'redis' => [
        'driver' => \TinyFramework\Session\RedisSession::class,
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => (int)env('REDIS_PORT', 6379),
        'password' => env('REDIS_PASSWORD', null),
        'database' => (int)env('REDIS_SESSION_DATABASE', 0),
        'read_write_timeout' => (int)env('REDIS_READ_WRITE_TIMEOUT', -1),
        'prefix' => env('REDIS_SESSION_PREFIX', 'tinyframework:session:'),
    ]
];
