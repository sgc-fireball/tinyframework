<?php declare(strict_types=1);

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

return [
    'env' => env('APP_ENV', 'production'),

    'debug' => toBool(env('APP_DEBUG', false)),

    'cache' => env('APP_CACHE', true),

    'url' => env('APP_URL', 'http://localhost'),

    'secret' => env('APP_SECRET', null),
];
