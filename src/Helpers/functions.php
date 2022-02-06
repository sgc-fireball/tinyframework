<?php

declare(strict_types=1);

use TinyFramework\Cache\CacheInterface;
use TinyFramework\Core\Config;
use TinyFramework\Core\ConfigInterface;
use TinyFramework\Core\Container;
use TinyFramework\Core\DotEnvInterface;
use TinyFramework\Crypt\CryptInterface;
use TinyFramework\Database\DatabaseInterface;
use TinyFramework\Event\EventAwesome;
use TinyFramework\Event\EventDispatcherInterface;
use TinyFramework\Hash\HashInterface;
use TinyFramework\Helpers\Arr;
use TinyFramework\Helpers\Htmlable;
use TinyFramework\Helpers\Str;
use TinyFramework\Http\Response;
use TinyFramework\Http\Router;
use TinyFramework\Localization\TranslatorInterface;
use TinyFramework\Logger\LoggerInterface;
use TinyFramework\Mail\MailerInterface;
use TinyFramework\Queue\QueueInterface;
use TinyFramework\Session\SessionInterface;
use TinyFramework\Template\ViewInterface;
use TinyFramework\Validation\ValidatorInterface;

if (!function_exists('root_dir')) {
    function root_dir(): string
    {
        if (\defined('ROOT')) {
            return ROOT;
        }
        $dir = realpath(__DIR__ . '/../..');
        if (strpos($dir, '/vendor/')) {
            $dir = preg_replace('/\/vendor\/.*$/', '', $dir);
        }
        define('ROOT', $dir);
        return $dir;
    }
}

if (!function_exists('public_dir')) {
    function public_dir(): string
    {
        return root_dir() . DIRECTORY_SEPARATOR . 'public';
    }
}

if (!function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {
    function base64url_decode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), \strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

if (!function_exists('container')) {
    /**
     * @param string|null $key
     * @param array $parameters
     * @return mixed|TinyFramework\Core\Container
     */
    function container(?string $key = null, array $parameters = []): mixed
    {
        $container = Container::instance();
        if ($key === null) {
            return $container;
        }
        if (!empty($parameters)) {
            return $container->call($key, $parameters);
        }
        return $container->get($key);
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, mixed $value = null): ConfigInterface|array|string|bool|int|null
    {
        $config = container('config');
        assert($config instanceof ConfigInterface);
        if ($key === null) {
            return $config;
        }
        if ($value !== null) {
            return $config->set($key, $value);
        }
        return $config->get($key);
    }
}

if (!function_exists('cache')) {
    function cache(): CacheInterface
    {
        return container('cache');
    }
}

if (!function_exists('database')) {
    function database(): DatabaseInterface
    {
        return container('database');
    }
}

if (!function_exists('session')) {
    function session(): SessionInterface
    {
        return container('session');
    }
}

if (!function_exists('event')) {
    function event(EventAwesome $event = null): EventDispatcherInterface
    {
        $dispatcher = container('event');
        assert($dispatcher instanceof EventDispatcherInterface);
        return $event ? $dispatcher->dispatch($event) : $dispatcher;
    }
}

if (!function_exists('logger')) {
    function logger(): LoggerInterface
    {
        return container('logger');
    }
}

if (!function_exists('queue')) {
    function queue(): QueueInterface
    {
        return container('queue');
    }
}

if (!function_exists('hasher')) {
    function hasher(): HashInterface
    {
        return container('hash');
    }
}

if (!function_exists('crypto')) {
    function crypto(): CryptInterface
    {
        return container('crypt');
    }
}

if (!function_exists('mailer')) {
    function mailer(): MailerInterface
    {
        return container('mailer');
    }
}

if (!function_exists('view')) {
    /**
     * @param string|null $file
     * @param array $data
     * @param int $code
     * @param array $headers
     * @return Response|ViewInterface
     */
    function view(string $file = null, array $data = [], int $code = 200, array $headers = []): mixed
    {
        $view = container('view');
        assert($view instanceof ViewInterface);
        if ($file === null) {
            return $view;
        }
        $response = $view->render($file, $data);
        $response = Response::new($response, $code);
        return $response->headers($headers);
    }
}

if (!function_exists('running_in_console')) {
    function running_in_console(): bool
    {
        return PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
    }
}

if (!function_exists('dump')) {
    /**
     * @param mixed ...$val
     * @return void
     */
    function dump(...$val): void
    {
        foreach ($val as $value) {
            echo running_in_console() ? '' : '<code><pre>';
            var_dump($value);
            echo running_in_console() ? '' : '</pre></code>';
        }
    }
}

if (!function_exists('dd')) {
    /**
     * @param mixed ...$val
     * @return void
     */
    function dd(...$val): void
    {
        \call_user_func_array('dump', $val);
        running_in_console() ? exit(1) : die();
    }
}

if (!function_exists('env')) {
    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    function env(string $key, $default = null): mixed
    {
        $env = container(DotEnvInterface::class);
        assert($env instanceof DotEnvInterface);
        return $env->get($key) ?? $default;
    }
}

if (!function_exists('exception2text')) {
    function exception2text(Throwable $e, bool $stacktrace = false): string
    {
        $result = sprintf(
            '%s[%d] %s in %s:%d',
            \get_class($e),
            $e->getCode(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        if ($stacktrace) {
            $result .= "\n" . $e->getTraceAsString();
        }
        if ($e = $e->getPrevious()) {
            $result .= sprintf("\n - %s", exception2text($e, $stacktrace));
        }
        $result = str_replace(root_dir(), '', $result);
        return $result;
    }
}

if (!function_exists('guid')) {
    function guid(): string
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $data = (string)openssl_random_pseudo_bytes(16);
            $data[6] = \chr(\ord($data[6]) & 0x0f | 0x40); // set version to 0100
            $data[8] = \chr(\ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }
        if (function_exists('random_bytes')) {
            $data = (string)random_bytes(16);
            $data[6] = \chr(\ord($data[6]) & 0x0f | 0x40); // set version to 0100
            $data[8] = \chr(\ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }
        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            random_int(0, 65535),
            random_int(0, 65535),
            random_int(0, 65535),
            random_int(16384, 20479),
            random_int(32768, 49151),
            random_int(0, 65535),
            random_int(0, 65535),
            random_int(0, 65535)
        );
    }
}

if (!function_exists('trans')) {
    function trans(string $key, array $values = [], string $locale = null): string
    {
        $translator = container('translator');
        assert($translator instanceof TranslatorInterface);
        return $translator->trans($key, $values, $locale);
    }
}

if (!function_exists('trans_choice')) {
    function trans_choice(string $key, int $count, array $values = [], string $locale = null): string
    {
        $translator = container('translator');
        assert($translator instanceof TranslatorInterface);
        return $translator->transChoice($key, $count, $values, $locale);
    }
}

if (!function_exists('route')) {
    function route(string $name, array $parameters = []): string
    {
        $router = container('router');
        assert($router instanceof Router);
        return $router->path($name, $parameters);
    }
}

if (!function_exists('url')) {
    function url(string $path = '/', array $parameters = []): string
    {
        $router = container('router');
        assert($router instanceof Router);
        return $router->url($path, $parameters);
    }
}

if (!function_exists('validator')) {
    function validator(): ValidatorInterface
    {
        return container('validator');
    }
}

if (!function_exists('asset_version')) {
    function asset_version(string $path): string
    {
        $filepath = str_contains($path, '?') ? substr($path, 0, strpos($path, '?')) : $path;
        $filepath = public_dir() . DIRECTORY_SEPARATOR . ltrim($filepath, '/');
        return !file_exists($filepath) ? $path : url(
            $path,
            ['_' => base_convert((string)filemtime($filepath), 10, 36)]
        );
    }
}

if (!function_exists('to_bool')) {
    function to_bool(mixed $mixed): bool
    {
        $mixed = \is_string($mixed) && \in_array(mb_strtolower($mixed), ['y', 'yes', 'true', 'on'])
            ? true
            : $mixed;
        $mixed = \is_string($mixed) && \in_array(mb_strtolower($mixed), ['n', 'no', 'false', 'off', 'null'])
            ? false
            : $mixed;
        return (bool)$mixed;
    }
}

if (!function_exists('e')) {
    function e(Htmlable|string $content): string
    {
        if ($content instanceof Htmlable) {
            return (string)$content->html();
        }
        return htmlspecialchars($content, ENT_QUOTES, "UTF-8", true);
    }
}

if (!function_exists('password')) {
    function password(
        int  $length = 16,
        bool $lowerChars = true,
        bool $upperChars = true,
        bool $numbers = true,
        bool $symbols = true
    ): string
    {
        $password = '';
        $chars = '';
        if ($lowerChars) {
            $chars .= 'abcdefghkmnpqrstwxyz';
        }
        if ($upperChars) {
            $chars .= 'ABCDEFGHKMNPQRSTWXYZ';
        }
        if ($numbers) {
            $chars .= '2345689';
        }
        if ($symbols) {
            $chars .= ';#$*-/<=>?@^_|~';
        }
        if (empty($chars)) {
            throw new RuntimeException('Please allow minimum one of the following sets: lowerChars, upperChars, numbers, symbols.');
        }
        $counts = \strlen($chars) - 1;
        while (\strlen($password) < $length) {
            $chars = str_shuffle($chars);
            $password .= substr($chars, random_int(0, $counts), 1);
        }
        return $password;
    }
}

if (!function_exists('console_size')) {
    function console_size(): array
    {
        return explode(' ', @exec('stty size 2>/dev/null') ?: '80 50');
    }
}

if (!function_exists('size_format')) {
    function size_format(float $byte, int $precision = 2): string
    {
        $steps = [
            ['size' => 1024, 'type' => 'KB'],
            ['size' => 1024, 'type' => 'MB'],
            ['size' => 1024, 'type' => 'GB'],
            ['size' => 1024, 'type' => 'TB'],
            ['size' => 1024, 'type' => 'PB'],
        ];
        $type = 'B';
        foreach ($steps as $step) {
            if ($byte < $step['size']) {
                break;
            }
            $byte /= $step['size'];
            $type = $step['type'];
        }
        return sprintf('%.' . abs($precision) . 'f %s', $byte, $type);
    }
}

if (!function_exists('time_format')) {
    function time_format(float|int $seconds): string
    {
        if ($seconds <= 1) {
            return '1 sec';
        }
        if ($seconds <= 60) {
            return sprintf('%d secs', $seconds);
        }
        $minutes = $seconds / 60;
        $seconds = $seconds % 60;
        if ($minutes <= 60) {
            return sprintf('%dm%ds', $minutes, $seconds);
        }
        $hours = $minutes / 60;
        $minutes = $minutes % 60;
        return sprintf('%dh%dm', $hours, $minutes);
    }
}

if (!function_exists('vnsprintf')) {
    function vnsprintf(string $format, array $args, string $pattern = "/\{(\w+)(:([^\}]+))?\}/"): string
    {
        return preg_replace_callback($pattern, function ($matches) use ($args) {
            return sprintf($matches[3] ?? '%s', @$args[$matches[1]] ?: '');
        }, $format);
    }
}

if (!function_exists('tmpreaper')) {
    function tmpreaper(string $folder, int $expire): void
    {
        if (!is_dir($folder)) {
            return;
        }
        $folder = rtrim(realpath($folder), '/');
        $fileSystemIterator = new FilesystemIterator($folder);
        foreach ($fileSystemIterator as $file) {
            if ($file->getCTime() < $expire) {
                unlink($folder . '/' . $file->getFilename());
            }
        }
    }
}

if (!function_exists('data_get')) {
    function data_get(mixed $target, mixed $key, string $delimiter = '.'): mixed
    {
        // exact match
        if (\is_array($target) && \array_key_exists($key, $target)) {
            return $target[$key];
        } elseif (\is_object($target) && property_exists($target, $key)) {
            return $target[$key];
        }

        assert(!empty($delimiter), 'Parameter #3 $delimiter of function explode expects non-empty-string.');
        // deep search
        $keys = \is_array($key) ? $key : explode($delimiter, $key);
        foreach ($keys as $key) {
            if (\is_array($target) && \array_key_exists($key, $target)) {
                $target = &$target[$key];
            } elseif (\is_object($target) && property_exists($target, $key)) {
                $target = &$target[$key];
            } else {
                return null;
            }
        }
        return $target;
    }
}

if (!function_exists('class_basename')) {
    function class_basename(string|object $class): string
    {
        $class = \is_object($class) ? \get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('inMaintenanceMode')) {
    function inMaintenanceMode(): array|null
    {
        static $config;
        if (isset($config)) {
            return $config;
        }
        $config = null;
        $file = root_dir() . '/storage/maintenance.json';
        if (file_exists($file)) {
            $config = (array)json_decode(file_get_contents($file) ?: '[]', true) ?: [];
        }
        return $config;
    }
}

if (!function_exists('tap')) {
    function tap(object $object): object
    {
        return new class($object) {
            private object $object;

            public function __construct(object $object)
            {
                $this->object = $object;
            }

            public function __call(string $method, array $parameters): object
            {
                $this->object->{$method}(...$parameters);
                return $this;
            }
        };
    }
}
