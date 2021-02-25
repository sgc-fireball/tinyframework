<?php declare(strict_types=1);

$timezone = env('APP_TIMEZONE', 'UTC');
date_default_timezone_set($timezone);

return [
    'env' => env('APP_ENV', 'production'),

    'debug' => to_bool(env('APP_DEBUG', false)),

    'cache' => env('APP_CACHE', true),

    'url' => env('APP_URL', 'http://localhost'),

    'secret' => env('APP_SECRET', null),

    'locale' => env('APP_LANG', 'en'),

    'timezone' => $timezone,
];
