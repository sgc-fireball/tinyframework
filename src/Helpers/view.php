<?php declare(strict_types=1);

if (!function_exists('e')) {
    function e($content): string
    {
        return htmlspecialchars((string)$content, ENT_QUOTES, "UTF-8", true);
    }
}
