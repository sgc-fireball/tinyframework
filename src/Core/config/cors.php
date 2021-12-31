<?php

declare(strict_types=1);

return [

    /**
     * APP_URL isn't needed here because it isn't a cors request
     */
    'allow_origins' => explode(',', env('CORS_ALLOW_ORIGINS') ?? ''),

    'max_age' => env('CORS_MAX_AGE') ?? 60,

    'allow_credentials' => to_bool(env('CORS_ALLOW_CREDENTIALS') ?? false),

];
