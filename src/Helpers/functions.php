<?php

declare(strict_types=1);

use TinyFramework\Broadcast\BroadcastInterface;
use TinyFramework\Cache\CacheInterface;
use TinyFramework\Core\ConfigInterface;
use TinyFramework\Core\Container;
use TinyFramework\Core\DotEnvInterface;
use TinyFramework\Core\KernelInterface;
use TinyFramework\Crypt\CryptInterface;
use TinyFramework\Database\BaseModel;
use TinyFramework\Database\DatabaseInterface;
use TinyFramework\Database\QueryInterface;
use TinyFramework\Event\EventAwesome;
use TinyFramework\Event\EventDispatcherInterface;
use TinyFramework\FileSystem\FileSystemInterface;
use TinyFramework\Hash\HashInterface;
use TinyFramework\Helpers\Arr;
use TinyFramework\Helpers\Htmlable;
use TinyFramework\Helpers\Str;
use TinyFramework\Helpers\Uuid;
use TinyFramework\Http\Middleware\SessionMiddleware;
use TinyFramework\Http\RequestInterface;
use TinyFramework\Http\Response;
use TinyFramework\Http\Router;
use TinyFramework\Helpers\DateTime;
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
    function public_dir(string $file = ''): string
    {
        $file = ltrim($file, DIRECTORY_SEPARATOR);
        $rootDir = root_dir();
        if (defined('PHARBIN') && PHARBIN) {
            $rootDir = implode(DIRECTORY_SEPARATOR, [$_SERVER['HOME'], '.config', 'tinyframework']);
        }
        return rtrim($rootDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $file, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('storage_dir')) {
    function storage_dir(string $file = ''): string
    {
        $file = ltrim($file, DIRECTORY_SEPARATOR);
        $rootDir = root_dir();
        if (defined('PHARBIN') && PHARBIN) {
            $rootDir = implode(DIRECTORY_SEPARATOR, [$_SERVER['HOME'], '.config', 'tinyframework']);
        }
        return rtrim($rootDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $file, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('base64_to_base64url')) {
    function base64_to_base64url(string $data): string
    {
        return rtrim(strtr($data, '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_to_base64')) {
    function base64url_to_base64(string $data): string
    {
        return str_pad(strtr($data, '-_', '+/'), \strlen($data) % 4, '=');
    }
}

if (!function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return base64_to_base64url(base64_encode($data));
    }
}

if (!function_exists('base64url_decode')) {
    function base64url_decode(string $data, bool $strict = false): string
    {
        return base64_decode(base64url_to_base64($data), $strict);
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
        static $config;
        if (!isset($config)) {
            $config = container('config');
            assert($config instanceof ConfigInterface);
        }
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
    function cache(?string $name = null): CacheInterface
    {
        return container('cache' . ($name ? '.' . $name : ''));
    }
}

if (!function_exists('filesystem')) {
    function filesystem(?string $name = null): FileSystemInterface
    {
        return container('filesystem' . ($name ? '.' . $name : ''));
    }
}

if (!function_exists('database')) {
    function database(?string $name = null): DatabaseInterface
    {
        return container('database' . ($name ? '.' . $name : ''));
    }
}

if (!function_exists('session')) {
    function session(?string $name = null): SessionInterface
    {
        return container('session' . ($name ? '.' . $name : ''));
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
    function logger(?string $name = null): LoggerInterface
    {
        return container('logger' . ($name ? '.' . $name : ''));
    }
}

if (!function_exists('queue')) {
    function queue(?string $name = null): QueueInterface
    {
        return container('queue' . ($name ? '.' . $name : ''));
    }
}

if (!function_exists('hasher')) {
    function hasher(?string $name = null): HashInterface
    {
        return container('hash' . ($name ? '.' . $name : ''));
    }
}

if (!function_exists('crypto')) {
    function crypto(?string $name = null): CryptInterface
    {
        return container('crypt' . ($name ? '.' . $name : ''));
    }
}

if (!function_exists('mailer')) {
    function mailer(?string $name = null): MailerInterface
    {
        return container('mailer' . ($name ? '.' . $name : ''));
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
    function dump(mixed ...$val): void
    {
        foreach ($val as $value) {
            echo running_in_console() ? '' : '<code><pre>';
            var_dump($value);
            echo running_in_console() ? '' : '</pre></code>';
        }
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$val): never
    {
        \call_user_func_array('dump', $val);
        running_in_console() ? exit(1) : die();
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        static $env;
        if (!isset($env)) {
            $env = container(DotEnvInterface::class);
            assert($env instanceof DotEnvInterface);
        }
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
        if (root_dir() === '/') {
            $result = preg_replace('/phar:\/\/.*\/src/', 'src', $result);
            $result = preg_replace('/phar:\/\/.*\/vendor/', 'vendor', $result);
        } else {
            $result = str_replace(root_dir(), '', $result);
        }
        return $result;
    }
}

if (!function_exists('guid')) {
    function guid(): string
    {
        return Uuid::v6();
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
        static $mapping;
        if (!isset($mapping)) {
            $mapping = [];
            $mixFile = root_dir() . '/mix-manifest.json';
            if (file_exists($mixFile) && is_readable($mixFile)) {
                foreach ((array)json_decode(file_get_contents($mixFile), true) as $source => $target) {
                    $mapping[str_replace('/public/', '', $source)] = str_replace('/public/', '', $target);
                }
            }
        }

        $filepath = str_contains($path, '?') ? substr($path, 0, strpos($path, '?')) : $path;
        if (array_key_exists($filepath, $mapping)) {
            $filepath = $mapping[$filepath];
        }

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

if (!function_exists('toBool')) {
    function toBool(mixed $mixed): bool
    {
        return to_bool($mixed);
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
        int $length = 16,
        bool $lowerChars = true,
        bool $upperChars = true,
        bool $numbers = true,
        bool $symbols = true
    ): string {
        $password = '';
        $chars = '';
        if ($lowerChars) {
            $chars .= 'abcdefghkmnpqrstwxyz'; // without i,j,l,o,u,v
        }
        if ($upperChars) {
            $chars .= 'ABCDEFGHJKLMNPQRSTWXYZ'; // without I,O,U,V
        }
        if ($numbers) {
            $chars .= '2345689'; // without 0,1
        }
        if ($symbols) {
            $chars .= ';#$*-/<=>?@^_|~';
        }
        if (empty($chars)) {
            throw new RuntimeException(
                'Please allow minimum one of the following sets: lowerChars, upperChars, numbers, symbols.'
            );
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
        $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        for ($i = 0; $byte >= 1024 && $i < count($unit) - 1; $i++) {
            $byte /= 1024;
        }
        return round($byte, $precision) . ' ' . $unit[$i];
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

        assert(!empty($delimiter), 'Parameter #3 $delimiter of function data_get expects non-empty-string.');
        // deep search
        $keys = \is_array($key) ? $key : explode($delimiter, $key);
        foreach ($keys as $key) {
            if (\is_array($target) && \array_key_exists($key, $target)) {
                $target = &$target[$key];
            } elseif (\is_array($target) && $key === '*') {
                $target = array_values($target);
            } elseif (\is_object($target) && property_exists($target, $key)) {
                $target = &$target[$key];
            } elseif (\is_object($target) && $key === '*') {
                $target = array_values(get_object_vars($target));
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
        $kernel = container('kernel');
        assert($kernel instanceof KernelInterface);
        return $kernel->getMaintenanceConfig();
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

if (!function_exists('str')) {
    function str(string $string): Str
    {
        return Str::factory($string);
    }
}

if (!function_exists('arr')) {
    function arr(array $arr): Arr
    {
        return Arr::factory($arr);
    }
}

if (!function_exists('collect')) {
    function collect(array $arr): Arr
    {
        return Arr::factory($arr);
    }
}

if (!function_exists('broadcast')) {
    function broadcast(string $channel, array $message): void
    {
        $broadcast = container(BroadcastInterface::class);
        assert($broadcast instanceof BroadcastInterface);
        $broadcast->publish($channel, $message);
    }
}

if (!function_exists('assignEagerLoadingPaths')) {
    function assignEagerLoadingPaths(
        BaseModel|QueryInterface $model,
        array &$with,
        string|array $paths,
        callable $tester = null
    ): void {
        $realModel = $model;
        if ($model instanceof QueryInterface) {
            $class = $model->class();
            if (!$class) {
                throw new \RuntimeException('EagerLoading can only be used on models.');
            }
            $realModel = new $class();
        }
        $paths = is_string($paths) ? [$paths] : $paths;
        foreach ($paths as $key => $value) {
            if (is_integer($key)) {
                if (is_string($value)) {
                    $value = str_contains($value, '.') ? explode('.', $value) : [$value];
                    $subModel = $realModel;
                    $subWith = &$with;
                    foreach ($value as $subKey) {
                        if ($tester) {
                            $tester($subModel, $subKey);
                        }
                        $subWith[$subKey] ??= [];
                        $subWith = &$subWith[$subKey];
                        $subModel = $subModel->{$subKey}()->getBlankTargetObject();
                    }
                }
            } else {
                if ($tester) {
                    $tester($realModel, $key);
                }
                $with[$key] ??= [];
                assignEagerLoadingPaths(
                    $realModel->{$key}()->getBlankTargetObject(),
                    $with[$key],
                    $value,
                    $tester
                );
            }
        }
    }
}

if (!function_exists('isLuhnValid')) {
    function isLuhnValid(string|int $number): bool
    {
        $sanitized = preg_replace('/[- ]/', '', (string)$number);
        $sum = 0;
        $shouldDouble = null;
        for ($i = strlen($sanitized) - 1; $i >= 0; $i--) {
            $digit = substr($sanitized, $i, ($i + 1));
            $tmpNum = intval($digit);
            if ($shouldDouble) {
                $tmpNum *= 2;
                if ($tmpNum >= 10) {
                    $sum += (($tmpNum % 10) + 1);
                } else {
                    $sum += $tmpNum;
                }
            } else {
                $sum += $tmpNum;
            }
            $shouldDouble = !$shouldDouble;
        }
        return !!(($sum % 10) === 0 ? $sanitized : false);
    }
}

if (!function_exists('time_ms')) {
    function time_ms(): int
    {
        $timestamp = microtime(false);
        $unixts = intval(substr($timestamp, 11), 10);
        return $unixts * 1000 + intval(substr($timestamp, 2, 3), 10);
    }
}

if (!function_exists('time_us')) {
    function time_us(): int
    {
        $timestamp = microtime(false);
        $unixts = intval(substr($timestamp, 11), 10);
        return $unixts * 1000000 + intval(substr($timestamp, 2, 6), 10);
    }
}

if (!function_exists('time_ns')) {
    function time_ns(): int
    {
        $timestamp = microtime(false);
        $unixts = intval(substr($timestamp, 11), 10);
        return $unixts * 1000000000 + intval(substr($timestamp, 2, 8) . '0', 10);
    }
}

if (!function_exists('node')) {
    function node(): string
    {
        static $node;
        if (isset($node) && $node) {
            return $node;
        }

        $key = '__tinyframework_uuid_node';
        if (\function_exists('apcu_fetch')) {
            $node = apcu_fetch($key);
        }
        $node = substr(sha1(gethostname()), 0, 12,);
        if (\function_exists('apcu_store')) {
            apcu_store($key, $node);
        }
        return $node;
    }
}

if (!function_exists('command_exists')) {
    function command_exists(string $command): bool
    {
        $cmd = 'which ' . escapeshellarg($command) . ' 2>/dev/null';
        return !empty(`$cmd`);
    }
}

if (!function_exists('mimetype_from_file')) {
    function mimetype_from_file(string $path): string|null
    {
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (!$finfo) {
            return null;
        }
        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);
        return $mime ?: null;
    }
}

if (!function_exists('now')) {
    function now(DateTimeZone|string|null $timezone = null): DateTime
    {
        $timezone = is_null($timezone) ? config('app.timezone') ?: 'UTC' : $timezone;
        $timezone = is_string($timezone) ? new DateTimeZone($timezone) : $timezone;
        return new DateTime('now', $timezone);
    }
}

if (!function_exists('secret')) {
    function secret(): string
    {
        return base64_decode(config('app.secret'));
    }
}

if (!function_exists('userAgent')) {
    function userAgent(): string
    {
        static $cache;
        if (!isset($cache)) {
            $versions = [];
            if (defined('ROOT') && file_exists(ROOT . '/composer.json')) {
                $composer = json_decode(file_get_contents(ROOT . '/composer.json'), true);
                $version = sprintf(
                    '%s/%s',
                    preg_replace('/[^a-zA-Z0-9_-]/', '-', $composer['name']),
                    $composer['version'] ?? '0.1.0',
                );
                if (strpos($version, 'tinyframework') === false) {
                    $versions[] = $version;
                }
            }
            $tinyFrameworkVersion = '0.0';
            $tinyFrameworkPath = dirname(dirname(dirname((__FILE__)))) . '/composer.json';
            if (file_exists($tinyFrameworkPath)) {
                $tinyFrameworkVersion = json_decode(file_get_contents($tinyFrameworkPath), true)['version'];
            }
            $versions[] = 'TinyFramework/' . $tinyFrameworkVersion;
            $versions[] = '(' . gethostname() . '; PID:' . getmypid() . ')';
            $versions[] = 'PHP/' . phpversion();
            $cache = implode(' ', $versions);
        }
        return $cache;
    }
}

if (!function_exists('sigmoid')) {
    function sigmoid(float $t): float
    {
        return 1 / (1 + pow(M_EULER, -$t));
    }
}

if (!function_exists('datetime')) {
    function datetime(string $datetime = 'now', DateTimeZone|null $timezone = null): DateTime
    {
        return new DateTime($datetime, $timezone);
    }
}

if (!function_exists('old')) {
    function old(string $field, mixed $default = null): mixed
    {
        $request = container('request');
        assert($request instanceof RequestInterface);
        $flashInputs = $request->attribute(SessionMiddleware::FLASH_INPUTS);
        if (!is_array($flashInputs)) {
            return $default;
        }
        $result = $flashInputs[$field] ?? $default;
        $result = $request->post($field) ?? $result;
        return match (true) {
            is_string($result) => $result,
            is_numeric($result) => $result,
            default => (string)$result
        };
    }
}

if (!function_exists('error')) {
    function error(string $field): ?array
    {
        $request = container('request');
        assert($request instanceof RequestInterface);
        $flashErrors = $request->attribute(SessionMiddleware::FLASH_ERRORS);
        if (!is_array($flashErrors)) {
            return null;
        }
        return $flashErrors[$field] ?? null;
    }
}

if (!function_exists('getFlashMessages')) {
    function getFlashMessages(): array
    {
        $request = container('request');
        assert($request instanceof RequestInterface);
        $flashMessages = $request->attribute(SessionMiddleware::FLASH_MESSAGES);
        return is_array($flashMessages) ? $flashMessages : [];
    }
}

if (!function_exists('rescue')) {
    function rescue(callable|string|array|Closure $callback, mixed $default = null): mixed
    {
        try {
            $default = container()->call($callback);
        } catch (\Throwable $e) {
            // ignore
        }
        return $default;
    }
}
