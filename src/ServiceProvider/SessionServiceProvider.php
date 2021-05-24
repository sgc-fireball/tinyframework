<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\ContainerInterface;
use TinyFramework\Session\SessionInterface;

class SessionServiceProvider extends ServiceProviderAwesome
{

    public function register(): void
    {
        $globalConfig = $this->container->get('config')->get('session');

        $config = $globalConfig[$globalConfig['default']] ?? [];
        $config['ttl'] = $globalConfig['ttl'];
        $config['name'] = $globalConfig['name'] ?? 'session';
        $this->container
            ->alias('session', $config['driver'])
            ->alias(SessionInterface::class, $config['driver'])
            ->singleton($config['driver'], function () use ($config) {
                $class = $config['driver'];
                return new $class($config);
            });
    }

}
