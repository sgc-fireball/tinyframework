<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\ContainerInterface;
use TinyFramework\Logger\LoggerInterface;

class LoggerServiceProvider extends ServiceProviderAwesome
{

    public function register(): void
    {
        $config = $this->container->get('config')->get('logger');
        if ($config === null) {
            return;
        }
        $config = $config[$config['default']] ?? [];
        $this->container
            ->alias('logger', $config['driver'])
            ->alias(LoggerInterface::class, $config['driver'])
            ->singleton($config['driver'], function () use ($config) {
                $class = $config['driver'];
                return new $class($config);
            });
    }

}
