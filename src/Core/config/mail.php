<?php declare(strict_types=1);

return [
    'default' => 'smtp',
    'from' => [
        'email' => env('MAIL_FROM_EMAIL', null),
        'name' => env('MAIL_FROM_NAME', null),
    ],
    'null' => [
        'driver' => \Swift_Transport_NullTransport::class
    ],
    'smtp' => [
        'driver' => \Swift_SmtpTransport::class,
        'host' => env('SMTP_HOST', 'localhost'),
        'port' => env('SMTP_PORT', 25),
        'encryption' => env('SMTP_ENCRYPTION', 'tls'),
        'username' => env('SMTP_USERNAME', null),
        'password' => env('SMTP_PASSWORD', null),
    ],
    'sendmail' => [
        'driver' => \Swift_SendmailTransport::class,
        'command' => env('SMTP_SENDMAIL', '/usr/sbin/sendmail -bs')
    ]
];
