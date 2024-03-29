<?php

declare(strict_types=1);

return [
    'default' => env('BROADCAST_DRIVER', 'redis'),
    'global' => [
        'whitelist' => ['127.0.0.1', '::1'],
        'middleware' => [],
    ],
    'redis' => [
        'driver' => \TinyFramework\Broadcast\RedisBroadcast::class,
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => (int)env('REDIS_PORT', 6379),
        'password' => env('REDIS_PASSWORD', null),
        'database' => (int)env('REDIS_BROADCAST_DATABASE', 0),
        'read_write_timeout' => (int)env('REDIS_READ_WRITE_TIMEOUT', -1),
        'prefix' => env('REDIS_BROADCAST_PREFIX', 'tinyframework:broadcast:'),
    ],
    'swoole' => [
        'driver' => \TinyFramework\Broadcast\SwooleBroadcast::class,
    ],
];
