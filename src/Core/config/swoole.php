<?php

declare(strict_types=1);

$cpus = max(1, (int)shell_exec('egrep ^processor /proc/cpuinfo  | wc -l'));

return [
    'host' => env('SWOOLE_HOST', '127.0.0.1'),
    'port' => (int)env('SWOOLE_PORT', 9501),
    'mode' => SWOOLE_PROCESS,
    'sock_type' => SWOOLE_SOCK_TCP,

    'settings' => [
        /**
         * @link https://openswoole.com/docs/modules/swoole-server/configuration
         */
        'daemonize' => (bool)env('SWOOLE_DAEMONIZE', false),
        'pid_file' => root_dir() . '/storage/shell/swoole.pid',
        'max_request' => env('SWOOLE_MAX_REQUEST', 0),
        'chroot' => root_dir(),
        'worker_num' => env('SWOOLE_WORKER_NUM', $cpus),
        'reactor_num' => env('SWOOLE_WORKER_NUM', $cpus) * 2,
        'backlog' => env('SWOOLE_BACKLOG', 128),
        'enable_coroutine' => true,

        /** @link https://openswoole.com/docs/modules/swoole-server/configuration#dispatch_mode */
        'dispatch_mode' => env('SWOOLE_DISPATCH_MODE', 1),

        // Task worker
        'task_worker_num' => env('SWOOLE_TASK_WORKER_NUM', $cpus),
        'task_enable_coroutine' => true,

        // Logging
        'log_level' => 1,
        'log_file' => env('LOG_DIR', root_dir() . '/storage/logs') . '/swoole.log',
        'log_rotation' => SWOOLE_LOG_ROTATION_DAILY,
        'log_date_format' => '%Y-%m-%d %H:%M:%S',
        'log_date_with_microseconds' => false,

        // Protocol
        'open_http_protocol' => true,
        'open_http2_protocol' => false, // buggy!!!
        'open_websocket_protocol' => true,

        // Static Files
        'document_root' => root_dir() . '/public',
        'enable_static_handler' => true,
        'static_handler_locations' => [root_dir() . '/public'],
        'http_index_files' => [],

        // Compression
        'http_compression' => false,
        'websocket_compression' => false,
    ],
];
