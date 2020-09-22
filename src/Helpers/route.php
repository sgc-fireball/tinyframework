<?php declare(strict_types=1);

if (!function_exists('route')) {
    function route(string $name = null, array $parameters = []): string
    {
        return container('router')->path($name, $parameters);
    }
}
