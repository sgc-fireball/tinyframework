<?php

declare(strict_types=1);

$cpus = max(1, (int)shell_exec('egrep ^processor /proc/cpuinfo  | wc -l'));

$config = [
    'host' => env('SWOOLE_HOST', '127.0.0.1'),
    'port' => (int)env('SWOOLE_PORT', 9501),
    'mode' => SWOOLE_PROCESS,
    'sock_type' => SWOOLE_SOCK_TCP,

    'settings' => [
        /**
         * @link https://openswoole.com/docs/modules/swoole-server/configuration
         */
        'daemonize' => (bool)env('SWOOLE_DAEMONIZE', false),
        'pid_file' => storage_dir('shell/swoole.pid'),
        'max_request' => env('SWOOLE_MAX_REQUEST', 0),
        'chroot' => defined('PHARBIN') && PHARBIN ? sys_get_temp_dir() : root_dir(),
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
        'log_file' => env('LOG_DIR', storage_dir('logs')) . '/swoole.log',
        'log_rotation' => SWOOLE_LOG_ROTATION_DAILY,
        'log_date_format' => '%Y-%m-%d %H:%M:%S',
        'log_date_with_microseconds' => false,

        // Protocol
        'open_http_protocol' => true,
        'open_http2_protocol' => false, // buggy!!!
        'open_websocket_protocol' => true,

        // Static Files
        'document_root' => public_dir(),
        'enable_static_handler' => true,
        'static_handler_locations' => [public_dir()],
        'http_index_files' => [],

        // Compression
        'http_compression' => false,
        'websocket_compression' => false,
    ],
];

$logFile = $config['settings']['log_file'];
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0750, true);
}
if (!file_exists($logFile)) {
    touch($logFile);
}

$publicDir = $config['settings']['document_root'];
if (!is_dir($publicDir)) {
    mkdir($publicDir, 0750, true);
}

return $config;
