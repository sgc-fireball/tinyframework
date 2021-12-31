<?php

declare(strict_types=1);

$key = env('APP_SECRET', null);
$key = $key ? base64_decode($key) : null;

return [
    'default' => env('CRYPT_DRIVER', 'aes256cbc'),
    'aes256cbc' => [
        'driver' => \TinyFramework\Crypt\AES256CBC::class,
        'key' => $key
    ]
];
