<?php declare(strict_types=1);

namespace TinyFramework\Core;

use TinyFramework\Console\Output\Output;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Http\Response;
use TinyFramework\ServiceProvider\ConsoleServiceProvider;
use TinyFramework\ServiceProvider\CryptServiceProvider;
use TinyFramework\ServiceProvider\HashServiceProvider;
use TinyFramework\ServiceProvider\MailServiceProvider;
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

abstract class Kernel implements KernelInterface
{

    protected ContainerInterface $container;

    /** @var string[] */
    protected array $serviceProviderNames = [];

    /** @var ServiceProviderInterface[] */
    protected array $serviceProviders = [];

    public function __construct(ContainerInterface $container)
    {
        ini_set('display_errors', 'Off');
        error_reporting(-1);
        $this->container = $container;
        $this->container->get(DotEnvInterface::class)->load('.env')->load('.env.local');
        $this->container->alias('kernel', Kernel::class)->singleton(Kernel::class, $this);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
        $this->findServiceProviders();
        $this->register();
        $this->boot();
    }

    protected function findServiceProviders()
    {
        $this->serviceProviderNames = [
            EventServiceProvider::class,
            ConfigServiceProvider::class,
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
        ];
        if ($this->runningInConsole()) {
            $this->serviceProviderNames[] = ConsoleServiceProvider::class;
        }

        if (file_exists('composer.lock')) {
            if ($content = file_get_contents('composer.lock')) {
                $composer = json_decode($content, true);
                if (array_key_exists('packages', $composer)) {
                    foreach ($composer['packages'] as $package) {
                        if (array_key_exists('extra', $package)) {
                            if (array_key_exists('tinyframework', $package['extra'])) {
                                if (array_key_exists('providers', $package['extra']['tinyframework'])) {
                                    $this->serviceProviderNames += $package['extra']['tinyframework']['providers'];
                                }
                            }
                        }
                    }
                }
            }
        }

        $root = (defined('ROOT') ? ROOT : '.');
        if (is_dir($root . '/app/Providers')) {
            foreach (glob($root . '/app/Providers/*.php') as $file) {
                $provider = 'App\\Providers\\' . str_replace('.php', '', basename($file));
                if (class_exists($provider)) {
                    $this->serviceProviderNames[] = $provider;
                }
            }
        }
    }

    protected function register()
    {
        /** @var string $serviceProvider */
        foreach ($this->serviceProviderNames as $serviceProvider) {
            $this->serviceProviders[] = $serviceProvider = (new $serviceProvider($this->container));
            /** @var ServiceProviderInterface $serviceProvider */
            $serviceProvider->register();
        }
    }

    protected function boot()
    {
        foreach ($this->serviceProviders as &$serviceProvider) {
            /** @var ServiceProviderInterface $serviceProvider */
            $serviceProvider->boot();
        }
    }

    public function runningInConsole(): bool
    {
        return runningInConsole();
    }

    public function handleError(int $level, string $message, string $file = '', int $line = 0, array $context = [])
    {
        if (error_reporting() & $level) {
            $this->handleException(new \ErrorException($message, 0, $level, $file, $line));
        }
    }

    abstract public function handleException(\Throwable $e);

    public function handleShutdown()
    {
        $error = error_get_last();
        if (!$error || !in_array($error['type'], [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE])) {
            return;
        }
        $this->handleException(new \RuntimeException($error['message'], 0));
    }

}
