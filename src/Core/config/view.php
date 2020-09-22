<?php declare(strict_types=1);

return [
    'default' => 'blade',
    'blade' => [
        'driver' => \TinyFramework\Template\Blade::class,
        'cache' => env('BLADE_CACHE', env('APP_CACHE', true)),
        'source' => 'ressources/views',
    ],
    'php' => [
        'driver' => \TinyFramework\Template\PHP::class,
    ],
];
