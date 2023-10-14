<?php

declare(strict_types=1);

namespace TinyFramework\Core;

use ErrorException;
use RuntimeException;
use TinyFramework\ServiceProvider\AuthServiceProvider;
use TinyFramework\ServiceProvider\BroadcastServiceProvider;
use TinyFramework\ServiceProvider\CacheServiceProvider;
use TinyFramework\ServiceProvider\ConfigServiceProvider;
use TinyFramework\ServiceProvider\ConsoleServiceProvider;
use TinyFramework\ServiceProvider\CryptServiceProvider;
use TinyFramework\ServiceProvider\DatabaseServiceProvider;
use TinyFramework\ServiceProvider\EventServiceProvider;
use TinyFramework\ServiceProvider\HashServiceProvider;
use TinyFramework\ServiceProvider\LocalizationServiceProvider;
use TinyFramework\ServiceProvider\LoggerServiceProvider;
use TinyFramework\ServiceProvider\MailServiceProvider;
use TinyFramework\ServiceProvider\QueueServiceProvider;
use TinyFramework\ServiceProvider\RouterServiceProvider;
use TinyFramework\ServiceProvider\ServiceProviderInterface;
use TinyFramework\ServiceProvider\SessionServiceProvider;
use TinyFramework\ServiceProvider\SwooleServiceProvider;
use TinyFramework\ServiceProvider\ValidationServiceProvider;
use TinyFramework\ServiceProvider\ViewServiceProvider;
use TinyFramework\ServiceProvider\XhprofServiceProvider;
use TinyFramework\StopWatch\StopWatch;

abstract class Kernel implements KernelInterface
{
    protected static ?string $reservedMemory;

    protected ContainerInterface $container;

    /** @var string[] */
    protected array $serviceProviderNames = [];

    /** @var ServiceProviderInterface[] */
    protected array $serviceProviders = [];

    protected StopWatch $stopWatch;

    public function __construct(ContainerInterface $container)
    {
        if (!defined('TINYFRAMEWORK_START')) {
            define('TINYFRAMEWORK_START', microtime(true));
        }
        if (!defined('TINYFRAMEWORK_START_AUTOLOAD')) {
            define('TINYFRAMEWORK_START_AUTOLOAD', microtime(true));
        }

        self::$reservedMemory = str_repeat('x', 10240); // reserve 10 kb

        $this->container = $container;
        $this->stopWatch = $this->resetStopWatch();
        $this->stopWatch->section('main')
            ->addEventPeriod(
                'fastcgi',
                'system',
                $_SERVER['REQUEST_TIME_FLOAT'] ?? TINYFRAMEWORK_START,
                TINYFRAMEWORK_START
            )
            ->addEventPeriod('autoload', 'composer', TINYFRAMEWORK_START, TINYFRAMEWORK_START_AUTOLOAD)
            ->addEventPeriod('kernel.start', 'kernel', TINYFRAMEWORK_START_AUTOLOAD, microtime(true));

        $this->stopWatch->start('kernel.register', 'kernel');
        $this->container
            ->alias('kernel', get_class($this))
            ->alias(Kernel::class, get_class($this))
            ->singleton(get_class($this), $this);
        /** @var DotEnvInterface $dotEnv */
        $dotEnv = $this->container
            ->alias('dotenv', DotEnvInterface::class)
            ->singleton(DotEnvInterface::class, DotEnv::class)
            ->get(DotEnvInterface::class);
        $dotEnv->load('.env')->load('.env.local');
        if (defined('SWOOLE') && SWOOLE) {
            $dotEnv->load('.env.swoole');
        }

        error_reporting($dotEnv->get('APP_ENV') === 'testing' ? E_ALL | E_NOTICE | E_DEPRECATED : 0);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleExceptionVoid']);
        register_shutdown_function([$this, 'handleShutdown']);
        $this->findServiceProviders();
        $this->register();
        $this->stopWatch->stop('kernel.register');

        $this->stopWatch->start('kernel.boot', 'kernel');
        $this->boot();
        $this->stopWatch->stop('kernel.boot');
    }

    protected function resetStopWatch(float $start = null): StopWatch
    {
        /** @var StopWatch $stopWatch */
        $stopWatch = $this->container->get(StopWatch::class);
        return $stopWatch->reset($start);
    }

    protected function findServiceProviders(): void
    {
        $this->serviceProviderNames = [
            EventServiceProvider::class,
            ConfigServiceProvider::class,
            XhprofServiceProvider::class,
            CryptServiceProvider::class,
            HashServiceProvider::class,
            LoggerServiceProvider::class,
            CacheServiceProvider::class,
            AuthServiceProvider::class,
            ViewServiceProvider::class,
            MailServiceProvider::class,
            DatabaseServiceProvider::class,
            RouterServiceProvider::class,
            SessionServiceProvider::class,
            QueueServiceProvider::class,
            BroadcastServiceProvider::class,
            LocalizationServiceProvider::class,
            ValidationServiceProvider::class,
            SwooleServiceProvider::class,
        ];
        if ($this->runningInConsole()) {
            $this->serviceProviderNames[] = ConsoleServiceProvider::class;
        }

        // @TODO implement a cache or composer hook!
        if (file_exists('composer.lock')) {
            if ($content = file_get_contents('composer.lock')) {
                $composer = json_decode($content, true);
                if (\array_key_exists('packages', $composer)) {
                    foreach ($composer['packages'] as $package) {
                        if (!array_key_exists('extra', $package)) {
                            continue;
                        }
                        if (!array_key_exists('tinyframework', $package['extra'])) {
                            continue;
                        }
                        if (!array_key_exists('providers', $package['extra']['tinyframework'])) {
                            continue;
                        }
                        if (!is_array($package['extra']['tinyframework']['providers'])) {
                            continue;
                        }
                        foreach ($package['extra']['tinyframework']['providers'] as $provider) {
                            $this->serviceProviderNames[] = ltrim($provider, '\\');
                        }
                    }
                }
            }
        }

        // @TODO implement load Providers by Namespace
        $root = root_dir();
        if (is_dir($root . '/app/Providers')) {
            $path = $root . '/app/Providers';
            $list = scandir($path); // allow real folders and .phar folders
            $list = array_filter($list, fn($f) => str_ends_with($f, '.php'));
            $list = array_map(fn($f) => $path . '/' . $f, $list);
            foreach ($list as $file) {
                $provider = 'App\\Providers\\' . str_replace('.php', '', basename($file));
                if (class_exists($provider)) {
                    $this->serviceProviderNames[] = $provider;
                } else {
                    throw new RuntimeException('Could not found service provider: ' . $provider);
                }
            }
        }
    }

    protected function register(): void
    {
        foreach ($this->serviceProviderNames as $serviceProvider) {
            assert(\is_string($serviceProvider));
            $this->serviceProviders[] = $serviceProvider = (new $serviceProvider($this->container));
            assert($serviceProvider instanceof ServiceProviderInterface);
            $serviceProvider->register();
        }
    }

    protected function boot(): void
    {
        foreach ($this->serviceProviders as &$serviceProvider) {
            assert($serviceProvider instanceof ServiceProviderInterface);
            $serviceProvider->boot();
        }
    }

    public function runningInConsole(): bool
    {
        return in_array(PHP_SAPI, ['cli', 'phpdbg'], true);
    }

    public function inMaintenanceMode(): bool
    {
        return file_exists(storage_dir('maintenance.json'));
    }

    public function getMaintenanceConfig(): array|null
    {
        if (!$this->inMaintenanceMode()) {
            return null;
        }
        return json_decode(file_get_contents(storage_dir('maintenance.json')) ?: '{}');
    }

    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (error_reporting() & $level) {
            $this->handleException(new ErrorException($message, 0, $level, $file, $line));
            return true;
        }
        return false;
    }

    /**
     * @internal
     */
    public function handleExceptionVoid(\Throwable $e): void
    {
        $this->handleException($e);
        die();
    }

    abstract public function handleException(\Throwable $e): int;

    public function handleShutdown(): void
    {
        $error = error_get_last();
        if (!$error || !in_array($error['type'], [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE])) {
            return;
        }
        $this->handleException(new RuntimeException($error['message'], 0));
    }
}
