<?php declare(strict_types=1);

use TinyFramework\Http\Response;

if (!function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {
    function base64url_decode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

if (!function_exists('container')) {
    /**
     * @param string|null $key
     * @param array $parameters
     * @return mixed|TinyFramework\Core\Container
     */
    function container(string $key = null, array $parameters = [])
    {
        return \TinyFramework\Core\Container::instance()->get($key, $parameters);
    }
}

if (!function_exists('config')) {
    /**
     * @param string|null $key
     * @return mixed|\TinyFramework\Core\Config
     */
    function config($key = null, $value = null)
    {
        $config = container('config');
        if (is_null($key)) {
            return $config;
        }
        if (!is_null($value)) {
            return $config->set($key, $value);
        }
        return $config->get($key);
    }
}

if (!function_exists('cache')) {
    function cache(): \TinyFramework\Cache\CacheInterface
    {
        return container('cache');
    }
}

if (!function_exists('session')) {
    function session(): \TinyFramework\Session\SessionInterface
    {
        return container('session');
    }
}

if (!function_exists('event')) {
    function event(): \TinyFramework\Event\EventDispatcherInterface
    {
        return container('event');
    }
}

if (!function_exists('logger')) {
    function logger(): \TinyFramework\Logger\LoggerInterface
    {
        return container('logger');
    }
}

if (!function_exists('queue')) {
    function queue(): \TinyFramework\Queue\QueueInterface
    {
        return container('queue');
    }
}

if (!function_exists('hash')) {
    function hash(): \TinyFramework\Hash\HashInterface
    {
        return container('hash');
    }
}

if (!function_exists('crypto')) {
    function crypto(): \TinyFramework\Crypt\CryptInterface
    {
        return container('crypt');
    }
}

if (!function_exists('view')) {
    /**
     * @param string|null $file
     * @param array $data
     * @param int $code
     * @param array $headers
     * @return \TinyFramework\Http\Response|\TinyFramework\Template\ViewInterface
     */
    function view(string $file = null, array $data = [], int $code = 200, array $headers = [])
    {
        /** @var \TinyFramework\Template\ViewInterface $view */
        $view = container('view');
        if (is_null($file)) {
            return $view;
        }
        $response = $view->render($file, $data);
        $response = Response::new($response, $code);
        return $response->headers($headers);
    }
}

if (!function_exists('runningInConsole')) {
    function runningInConsole(): bool
    {
        return \PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg';
    }
}

if (!function_exists('dump')) {
    /**
     * @param mixed ...$val
     * @return void
     */
    function dump(...$val)
    {
        foreach ($val as $value) {
            echo runningInConsole() ? '' : '<code><pre>';
            var_dump($value);
            echo runningInConsole() ? '' : '</pre></code>';
        }
    }
}

if (!function_exists('dd')) {
    /**
     * @param mixed ...$val
     * @return void
     */
    function dd(...$val)
    {
        call_user_func_array('dump', $val);
        exit(1);
    }
}

if (!function_exists('env')) {
    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? null;
        $value = is_string($value) && mb_strlen($value) === 0 ? null : $value;
        $value = is_string($value) && mb_strtolower($value) === 'null' ? null : $value;
        $value = is_string($value) && mb_strtolower($value) === 'true' ? true : $value;
        $value = is_string($value) && mb_strtolower($value) === 'false' ? false : $value;
        return $value ?? $default;
    }
}

if (!function_exists('exception2text')) {
    function exception2text(\Throwable $e, bool $stacktrace = false): string
    {
        $result = sprintf(
            '%s[%d] %s in %s:%d',
            get_class($e),
            $e->getCode(),
            str_replace([getcwd()], '', $e->getMessage()),
            ltrim(str_replace([getcwd()], '', $e->getFile()), '/'),
            $e->getLine()
        );
        if ($stacktrace) {
            $result .= "\n" . $e->getTraceAsString();
        }
        if ($e = $e->getPrevious()) {
            $result .= sprintf("\n - %s", exception2text($e, $stacktrace));
        }
        return $result;
    }
}

if (!function_exists('guid')) {
    function guid(): string
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            /** @var string $data */
            $data = openssl_random_pseudo_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }
}

if (!function_exists('trans')) {
    function trans(string $content, array $placeholder = []): string
    {
        // @TODO
        return vsprintf($content, $placeholder);
    }
}

if (!function_exists('route')) {
    function route(string $name = null, array $parameters = []): string
    {
        return container('router')->path($name, $parameters);
    }
}

if (!function_exists('toBool')) {
    function toBool($mixed): bool
    {
        $mixed = is_string($mixed) && in_array(mb_strtolower($mixed), ['y', 'yes', 'true', 'on']) ? true : $mixed;
        $mixed = is_string($mixed) && in_array(mb_strtolower($mixed), ['n', 'no', 'false', 'off', 'null']) ? false : $mixed;
        return (bool)$mixed;
    }
}

if (!function_exists('e')) {
    function e($content): string
    {
        return htmlspecialchars((string)$content, ENT_QUOTES, "UTF-8", true);
    }
}
