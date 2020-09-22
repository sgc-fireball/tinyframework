<?php declare(strict_types=1);

if (!function_exists('env')) {
    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}
