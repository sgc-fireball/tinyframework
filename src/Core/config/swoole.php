<?php

declare(strict_types=1);

defined('ROOT') ?: define('ROOT', getcwd());
defined('SWOOLE_PROCESS') ?: define('SWOOLE_PROCESS', 2);
defined('SWOOLE_SOCK_TCP') ?: define('SWOOLE_SOCK_TCP', 1);
defined('SWOOLE_LOG_ROTATION_DAILY') ?: define('SWOOLE_LOG_ROTATION_DAILY', 2);

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
        'daemonize' => 0,
        'chroot' => ROOT,
        'worker_num' => env('SWOOLE_WORKER_NUM', $cpus),
        'backlog' => env('SWOOLE_BACKLOG', 128),
        'enable_coroutine' => true,

        // Task worker
        'task_worker_num' => env('SWOOLE_TASK_WORKER_NUM', $cpus),

        // Logging
        'log_level' => 1,
        //'log_file' => env('LOG_DIR', root_dir() . '/storage/logs') . '/swoole.log',
        'log_rotation' => SWOOLE_LOG_ROTATION_DAILY,
        'log_date_format' => '%Y-%m-%d %H:%M:%S',
        'log_date_with_microseconds' => false,

        // Protocol
        'open_http_protocol' => true,
        'open_http2_protocol' => false, // buggy!!!
        'open_websocket_protocol' => true,

        // Static Files
        'document_root' => ROOT . '/public',
        'enable_static_handler' => true,
        'static_handler_locations' => [ROOT . '/public'],
        'http_index_files' => [],

        // Compression
        'http_compression' => false,
        'websocket_compression' => false,
    ],
];
