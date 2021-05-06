<?php declare(strict_types=1);

return [
    'default' => env('QUEUE_DRIVER', 'redis'),
    'sync' => [
        'driver' => \TinyFramework\Queue\SyncQueue::class,
    ],
    'redis' => [
        'driver' => \TinyFramework\Queue\RedisQueue::class,
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => (int)env('REDIS_PORT', 6379),
        'password' => env('REDIS_PASSWORD', null),
        'database' => (int)env('REDIS_QUEUE_DATABASE', 0),
        'read_write_timeout' => (int)env('REDIS_READ_WRITE_TIMEOUT', -1),
        'prefix' => env('REDIS_QUEUE_PREFIX', 'tinyframework:queue:')
    ]
];
