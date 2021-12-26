<?php declare(strict_types=1);

namespace TinyFramework\Core;

use ErrorException;
use RuntimeException;
use TinyFramework\ServiceProvider\BroadcastServiceProvider;
use TinyFramework\ServiceProvider\ConsoleServiceProvider;
use TinyFramework\ServiceProvider\CryptServiceProvider;
use TinyFramework\ServiceProvider\HashServiceProvider;
use TinyFramework\ServiceProvider\MailServiceProvider;
use TinyFramework\ServiceProvider\LocalizationServiceProvider;
use TinyFramework\ServiceProvider\ValidationServiceProvider;
use TinyFramework\ServiceProvider\ViewServiceProvider;
use TinyFramework\ServiceProvider\QueueServiceProvider;
use TinyFramework\ServiceProvider\ServiceProviderInterface;
use TinyFramework\ServiceProvider\CacheServiceProvider;
use TinyFramework\ServiceProvider\ConfigServiceProvider;
use TinyFramework\ServiceProvider\DatabaseServiceProvider;
use TinyFramework\ServiceProvider\EventServiceProvider;
use TinyFramework\ServiceProvider\LoggerServiceProvider;
use TinyFramework\ServiceProvider\RouterServiceProvider;
use TinyFramework\ServiceProvider\SessionServiceProvider;
use TinyFramework\ServiceProvider\XhprofServiceProvider;
use TinyFramework\StopWatch\StopWatch;

abstract class Kernel implements KernelInterface
{

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

        ini_set('display_errors', 'Off');
        error_reporting(-1);
        $this->container = $container;
        $this->stopWatch = $this->container
            ->alias('stopwatch', StopWatch::class)
            ->singleton(StopWatch::class, StopWatch::class)
            ->get(StopWatch::class);
        $this->stopWatch->section('main')
            ->addEventPeriod('0-fastcgi', 'system', $_SERVER['REQUEST_TIME_FLOAT'], TINYFRAMEWORK_START)
            ->addEventPeriod('1-autoload', 'composer', TINYFRAMEWORK_START, TINYFRAMEWORK_START_AUTOLOAD)
            ->addEventPeriod('2-kernel.start', 'kernel', TINYFRAMEWORK_START_AUTOLOAD, microtime(true));

        $this->stopWatch->start('3-kernel.register', 'kernel');
        $this->container
            ->alias('dotenv', DotEnvInterface::class)
            ->singleton(DotEnvInterface::class, DotEnv::class)
            ->get(DotEnvInterface::class)
            ->load('.env')->load('.env.local');
        $this->container
            ->alias('kernel', get_class($this))
            ->alias(Kernel::class, get_class($this))
            ->singleton(get_class($this), $this);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleExceptionVoid']);
        register_shutdown_function([$this, 'handleShutdown']);
        $this->findServiceProviders();
        $this->register();
        $this->stopWatch->stop('3-kernel.register');

        $this->stopWatch->start('4-kernel.boot', 'kernel');
        $this->boot();
        $this->stopWatch->stop('4-kernel.boot');
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
            ViewServiceProvider::class,
            MailServiceProvider::class,
            DatabaseServiceProvider::class,
            RouterServiceProvider::class,
            SessionServiceProvider::class,
            QueueServiceProvider::class,
            BroadcastServiceProvider::class,
            LocalizationServiceProvider::class,
            ValidationServiceProvider::class,
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
            foreach (glob($root . '/app/Providers/*.php') as $file) {
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
            #$name = sprintf('3.%02d-%s',$index,class_basename($serviceProvider));
            #$this->stopWatch->start($name, 'kernel');
            $this->serviceProviders[] = $serviceProvider = (new $serviceProvider($this->container));
            assert($serviceProvider instanceof ServiceProviderInterface);
            $serviceProvider->register();
            #$this->stopWatch->stop($name);
        }
    }

    protected function boot(): void
    {
        foreach ($this->serviceProviders as &$serviceProvider) {
            assert($serviceProvider instanceof ServiceProviderInterface);
            #$name = sprintf('4.%02d-%s',$index,class_basename($serviceProvider));
            #$this->stopWatch->start($name, 'kernel');
            $serviceProvider->boot();
            #$this->stopWatch->stop($name);
        }
    }

    public function runningInConsole(): bool
    {
        return running_in_console();
    }

    public function inMaintenanceMode(): bool
    {
        return file_exists('storage/maintenance.json');
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
