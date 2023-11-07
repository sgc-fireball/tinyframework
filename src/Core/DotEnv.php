<?php

declare(strict_types=1);

namespace TinyFramework\Core;

class DotEnv implements DotEnvInterface
{
    private static ?DotEnv $instance = null;

    private function __construct()
    {
        // Signalton
    }

    public static function instance(): DotEnv
    {
        if (!(self::$instance instanceof DotEnv)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function load(string $file): static
    {
        if (!file_exists($file)) {
            return $this;
        }
        $content = file_get_contents($file);
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = preg_replace('/\n+/', "\n", $content);
        $content = preg_replace('/^[^A-Z].*$/m', '', $content);
        foreach (explode("\n", $content) as $line) {
            if (mb_strpos($line, '=') < 1) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            if (mb_substr($value, 0, 1) === '"' && mb_substr($value, -1) === '"') {
                $value = mb_substr($value, 1, -1);
            }
            if (mb_substr($value, 0, 1) === "'" && mb_substr($value, -1) === "'") {
                $value = mb_substr($value, 1, -1);
            }
            $value = empty($value) ? 'null' : $value;
            putenv(sprintf('%s=%s', $key, $value));
            if (function_exists('apache_setenv')) {
                apache_setenv($key, $value);
            }
            $value = $this->convertValue($value);
            $_ENV[$key] = $_SERVER[$key] = $value;
        }
        foreach ($_ENV as $key => $value) {
            if (\is_string($_ENV[$key])) {
                while (preg_match('/\{(\w+)(:([^\}]+))?\}/', $_ENV[$key])) {
                    $_ENV[$key] = $_SERVER[$key] = vnsprintf($_ENV[$key], $_ENV);
                }
            }
        }
        return $this;
    }

    public function get(string $key): mixed
    {
        return $this->convertValue($_ENV[$key] ?? $_SERVER[$key] ?? null);
    }

    private function convertValue(mixed $value): mixed
    {
        /** @var null|string|bool|int|float $value */
        $value = \is_string($value) && empty($value) ? null : $value;
        $value = \is_string($value) && mb_strlen($value) === 0 ? null : $value;
        $value = \is_string($value) && mb_strtolower($value) === 'null' ? null : $value;
        $value = \is_string($value) && mb_strtolower($value) === 'empty' ? null : $value;
        $value = \is_string($value) && mb_strtolower($value) === 'true' ? true : $value;
        $value = \is_string($value) && mb_strtolower($value) === 'false' ? false : $value;
        return $value;
    }
}
