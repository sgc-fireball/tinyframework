<?php declare(strict_types=1);

namespace TinyFramework\Core;

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
        $this->container = $container;
        $this->container->get(DotEnvInterface::class)->load('.env')->load('.env.local');
        $this->container->alias('kernel', Kernel::class)->singleton(Kernel::class, $this);
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
            QueueServiceProvider::class
        ];

        $composer = json_decode(file_get_contents('composer.lock'), true);
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

}
