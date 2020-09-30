<?php declare(strict_types=1);

return [
    'default' => env('HASH_DRIVER', 'bcrypt'),
    'bcrypt' => [
        'driver' => \TinyFramework\Hash\BCrypt::class,
        'cost' => 10,
    ]
];
