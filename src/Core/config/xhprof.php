<?php declare(strict_types=1);

return [
    'enable' => extension_loaded('xhprof') ? to_bool(env('XHPROF_ENABLE', false)) : false,

    'percent' => env('XHPROF_PERCENT', 100),

    'dir' => env('XHPROF_DIR', root_dir() . '/storage/xhprof'),

    'expire' => 604800, // 7 days
];
