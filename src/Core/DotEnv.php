<?php declare(strict_types=1);

namespace TinyFramework\Core;

class DotEnv
{

    private static ?self $instance = null;

    private array $env = [
        'APP_ENV' => 'production',
        'APP_DEBUG' => false,
        'APP_URL' => 'http://localhost',
        'APP_SECRET' => null,
    ];

    private function __construct()
    {
    }

    public static function instance(): DotEnv
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function load(string $file)
    {
        if (!file_exists($file)) {
            return $this;
        }
        $content = file_get_contents($file);
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = preg_replace('/^[^A-Z].*$/m', '', $content);
        foreach (explode("\n", $content) as $line) {
            if (strpos($line, '=') < 1) {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);
            if (substr($value, 0, 1) === '"' && substr($value, -1) === '"') {
                $value = substr($value, 1, -1);
            }
            if (substr($value, 0, 1) === "'" && substr($value, -1) === "'") {
                $value = substr($value, 1, -1);
            }
            $value = $value === 'null' ? null : $value;
            $value = $value === 'true' ? true : $value;
            $value = $value === 'false' ? false : $value;
            $_ENV[$key] = $value;
        }
        return $this;
    }

}
