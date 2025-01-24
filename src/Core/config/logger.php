<?php

declare(strict_types=1);

return [
    'default' => env('LOG_DRIVER', 'file'),
    'file' => [
        'driver' => \TinyFramework\Logger\FileLogger::class,
        'path' => env('LOG_DIR', storage_dir('logs')),
    ],

    /**
     * Please use dio as default inside docker container
     */
    'dio' => [
        'driver' => \TinyFramework\Logger\DioLogger::class,
        'path' => '/proc/1/fd/1',
    ],
];
