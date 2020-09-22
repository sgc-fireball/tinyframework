<?php declare(strict_types=1);

return [
    'default' => env('LOG_DRIVER', 'file'),
    'file' => [
        'driver' => \TinyFramework\Logger\FileLogger::class,
        'path' => env('LOG_DIR', 'storage/logs')
    ]
];
