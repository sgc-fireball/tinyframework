<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Cache\ArrayCache;
use TinyFramework\Cache\CacheInterface;
use TinyFramework\Core\ContainerInterface;

class CacheServiceProvider extends ServiceProviderAwesome
{
    public function register(): void
    {
        $config = $this->container->get('config')->get('cache');
        if ($config === null) {
            return;
        }
        $config = $config[$config['default']] ?? [];
        $this->container
            ->alias('cache', $config['driver'])
            ->alias(CacheInterface::class, $config['driver'])
            ->singleton($config['driver'], function () use ($config) {
                if (!$this->container->get('config')->get('app.cache')) {
                    $config = ['driver' => ArrayCache::class];
                }
                $class = $config['driver'];
                return new $class($config);
            });
    }
}
