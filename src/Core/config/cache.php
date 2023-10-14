<?php

declare(strict_types=1);

return [
    'default' => env('CACHE_DRIVER', 'redis'),
    'none' => [
        'driver' => \TinyFramework\Cache\NoCache::class,
    ],
    'array' => [
        'driver' => \TinyFramework\Cache\ArrayCache::class,
    ],
    'file' => [
        'driver' => \TinyFramework\Cache\FileCache::class,
        'path' => env('CACHE_FILE_PATH', storage_dir('cache')),
    ],
    'redis' => [
        'driver' => \TinyFramework\Cache\RedisCache::class,
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => (int)env('REDIS_PORT', 6379),
        'password' => env('REDIS_PASSWORD', null),
        'database' => (int)env('REDIS_CACHE_DATABASE', 0),
        'read_write_timeout' => (int)env('REDIS_READ_WRITE_TIMEOUT', -1),
        'prefix' => env('REDIS_CACHE_PREFIX', 'tinyframework:cache:'),
    ],
    'swoole' => [
        'driver' => \TinyFramework\Cache\SwooleCache::class,
    ],
];
