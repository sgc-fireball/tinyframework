<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

class ImageOptimizer
{

    use Macroable;

    public static function optimize(string $path): bool
    {
        return match (mimetype_from_file($path)) {
            'image/jpeg' => self::optimizeJpeg($path),
            'image/png' => self::optimizePng($path),
            'image/gif' => self::optimizeGif($path),
            'image/webp' => self::optimizeWebP($path),
            'image/svg+xml', 'image/svg', 'text/plain', 'text/html' => self::optimizeSvg($path),
            default => false
        };
    }

    public static function optimizeJpeg(string $path): bool
    {
        if (mimetype_from_file($path) !== 'image/jpeg') {
            return false;
        }
        if (!command_exists('jpegoptim')) {
            trigger_error('Command jpegoptim not found.', E_USER_WARNING);
            return false;
        }
        $cmd = sprintf('jpegoptim %s 2>/dev/null >/dev/null', escapeshellarg($path));
        system($cmd, $exitCode);
        return $exitCode === 0;
    }

    public static function optimizePng(string $path): bool
    {
        if (mimetype_from_file($path) !== 'image/png') {
            return false;
        }
        if (!command_exists('optipng')) {
            trigger_error('Command optipng not found.', E_USER_WARNING);
            return false;
        }
        $cmd = sprintf('optipng %s 2>/dev/null >/dev/null', escapeshellarg($path));
        system($cmd, $exitCode1);

        if (!command_exists('pngquant')) {
            trigger_error('Command pngquant not found.', E_USER_WARNING);
            return false;
        }
        $cmd = sprintf(
            'pngquant %s --force --skip-if-larger --strip --output %s 2>/dev/null >/dev/null',
            escapeshellarg($path),
            escapeshellarg($path)
        );
        system($cmd, $exitCode2);

        return $exitCode1 === 0 || $exitCode2 === 0;
    }

    public static function optimizeSvg(string $path): bool
    {
        $mimetypes = ['text/html', 'image/svg', 'image/svg+xml', 'text/plain'];
        if (!in_array(mimetype_from_file($path), $mimetypes)) {
            return false;
        }
        if (!command_exists('svgo')) {
            trigger_error('Command svgo not found.', E_USER_WARNING);
            return false;
        }
        $cmd = sprintf(
            'svgo --input %s --output %s 2>/dev/null >/dev/null',
            escapeshellarg($path),
            escapeshellarg($path)
        );
        system($cmd, $exitCode);
        return $exitCode === 0;
    }

    public static function optimizeGif(string $path): bool
    {
        if (mimetype_from_file($path) !== 'image/gif') {
            return false;
        }
        if (!command_exists('gifsicle')) {
            trigger_error('Command gifsicle not found.', E_USER_WARNING);
            return false;
        }
        $cmd = sprintf(
            'gifsicle --colors=255 --optimize=3 --interlace %s --output %s 2>/dev/null >/dev/null',
            escapeshellarg($path),
            escapeshellarg($path)
        );
        system($cmd, $exitCode);
        return $exitCode === 0;
    }

    public static function optimizeWebP(string $path): bool
    {
        if (mimetype_from_file($path) !== 'image/webp') {
            return false;
        }
        if (!command_exists('cwebp')) {
            trigger_error('Command cwebp not found.', E_USER_WARNING);
            return false;
        }
        $cmd = sprintf(
            'cwebp %s -o %s 2>/dev/null >/dev/null',
            escapeshellarg($path),
            escapeshellarg($path)
        );
        system($cmd, $exitCode);
        return $exitCode === 0;
    }
}
