<?php declare(strict_types=1);

if (!function_exists('_')) {
    function _(string $content, array $placeholder = []): string
    {
        return vsprintf($content, $placeholder);
    }
}
