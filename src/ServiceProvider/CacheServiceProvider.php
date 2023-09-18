<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Cache\CacheInterface;
use TinyFramework\Cache\NoCache;

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
                    $config = ['driver' => NoCache::class];
                }
                $class = $config['driver'];
                return $this->container->call($class, ['config' => $config]);
            });
    }
}
