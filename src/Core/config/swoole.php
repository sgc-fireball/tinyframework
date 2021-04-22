<?php declare(strict_types=1);

if (!class_exists(\Swoole\Http\Server::class)) {
    return [];
}

return [
    'host' => env('SWOOLE_HOST', '127.0.0.1'),
    'port' => env('SWOOLE_PORT', 1080),
    'mode' => SWOOLE_PROCESS,
    'sock_type' => SWOOLE_SOCK_TCP, # | SWOOLE_SSL,

    'config' => [
        /** @https://www.swoole.co.uk/docs/modules/swoole-server/configuration */
        'user' => env('SWOOLE_USER', 'www-data'),
        'group' => env('SWOOLE_GROUP', 'www-data'),

        /** @see https://www.swoole.co.uk/docs/modules/swoole-server-set */
        'max_conn' => 1000,
        'daemonize' => 0,
        'reactor_num' => min(swoole_cpu_num() * 2 * 4, 12),
        'worker_num' => swoole_cpu_num() * 2,
        'max_request' => 1,

        // ssl
        'ssl_cert_file' => env('SWOOLE_SSL_CERT_FILE', '/etc/letsencrypt/live/hrdns.de/cert.pem'),
        'ssl_key_file' => env('SWOOLE_SSL_KEY_FILE', '/etc/letsencrypt/live/hrdns.de/privkey.pem'),
        'ssl_ciphers' => env('SWOOLE_SSL_CIPHERS', 'AES256+AESGCM+EECDH:AES256+AESGCM+EDH:!aNULL:!AES128'),
        'ssl_protocols' => (int)env('SWOOLE_SSL_PROTOCOLS', SWOOLE_SSL_TLSv1_2 | SWOOLE_SSL_TLSv1_3),
        'ssl_verify_peer' => to_bool(env('SWOOLE_SSL_VERIFY_PEER', false)),
        'ssl_allow_self_signed' => to_bool(env('SWOOLE_SSL_ALLOW_SELF_SIGNED', false)),

        // log
        'log_level' => (int)env('SWOOLE_LOG_LEVEL', 1),
        'log_file' => env('SWOOLE_LOGFILE', 'storage/logs/swoole.log'),
        'log_rotation' => (int)env('SWOOLE_LOG_ROTATION', SWOOLE_LOG_ROTATION_DAILY | SWOOLE_LOG_ROTATION_SINGLE),
        'log_date_format' => true, // or "day %d of %B in the year %Y. Time: %I:%S %p",
        'log_date_with_microseconds' => false,

        // protocol
        'open_http_protocol' => true,
        'open_http2_protocol' => true,
        'open_websocket_protocol' => false,
        'open_mqtt_protocol' => false,
    ],
];
