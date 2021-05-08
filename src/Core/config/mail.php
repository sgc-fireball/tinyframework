<?php declare(strict_types=1);

return [
    'default' => 'smtp',
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', null),
        'name' => env('MAIL_FROM_NAME', null),
    ],
    'smtp' => [
        'driver' => \TinyFramework\Mail\SmtpMailer::class,
        'host' => env('SMTP_HOST', 'localhost'),
        'port' => env('SMTP_PORT', 25),
        'encryption' => env('SMTP_ENCRYPTION', 'tls'),
        'username' => env('SMTP_USERNAME', null),
        'password' => env('SMTP_PASSWORD', null),
        'local_domain' => env('MAIL_HOST', '_'),
    ],
];
