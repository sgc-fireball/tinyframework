<?php declare(strict_types=1);

return [
    'default' => env('HASH_DRIVER', 'bcrypt'),

    'bcrypt' => [
        'driver' => \TinyFramework\Hash\BCrypt::class,
        'cost' => 10,
    ],

    'sha1' => [
        'driver' => \TinyFramework\Hash\HashFunction::class,
        'algorithm' => 'sha1'
    ],

    'sha256' => [
        'driver' => \TinyFramework\Hash\HashFunction::class,
        'algorithm' => 'sha256'
    ],

    'sha386' => [
        'driver' => \TinyFramework\Hash\HashFunction::class,
        'algorithm' => 'sha386'
    ],

    'sha512' => [
        'driver' => \TinyFramework\Hash\HashFunction::class,
        'algorithm' => 'sha512'
    ],

    'ripemd128' => [
        'driver' => \TinyFramework\Hash\HashFunction::class,
        'algorithm' => 'ripemd128'
    ],

    'ripemd160' => [
        'driver' => \TinyFramework\Hash\HashFunction::class,
        'algorithm' => 'ripemd160'
    ],

    'ripemd256' => [
        'driver' => \TinyFramework\Hash\HashFunction::class,
        'algorithm' => 'ripemd256'
    ],

    'ripemd320' => [
        'driver' => \TinyFramework\Hash\HashFunction::class,
        'algorithm' => 'ripemd320'
    ],

    'crc32' => [
        'driver' => \TinyFramework\Hash\HashFunction::class,
        'algorithm' => 'crc32'
    ],

];
