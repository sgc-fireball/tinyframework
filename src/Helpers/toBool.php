<?php declare(strict_types=1);

if (!function_exists('toBool')) {
    function toBool($mixed): bool
    {
        $mixed = is_string($mixed) && in_array(strtolower($mixed), ['y', 'yes', 'true', 'on']) ? true : $mixed;
        $mixed = is_string($mixed) && in_array(strtolower($mixed), ['n', 'no', 'false', 'off', 'null']) ? false : $mixed;
        return (bool)$mixed;
    }
}
