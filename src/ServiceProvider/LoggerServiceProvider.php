<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\ContainerInterface;
use TinyFramework\Logger\LoggerInterface;

class LoggerServiceProvider extends ServiceProviderAwesome
{

    public function register()
    {
        $config = $this->container->get('config')->get('logger');
        if (is_null($config)) {
            return;
        }
        $config = $config[$config['default']] ?? [];
        $this->container
            ->alias('logger', $config['driver'])
            ->alias(LoggerInterface::class, $config['driver'])
            ->singleton($config['driver'], function (ContainerInterface $container) use ($config) {
                $class = $config['driver'];
                return new $class($config);
            });
    }

}
