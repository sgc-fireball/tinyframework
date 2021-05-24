<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use Psr\Log\LoggerInterface;
use TinyFramework\Core\ContainerInterface;
use TinyFramework\Logger\FileLogger;
use TinyFramework\Queue\QueueInterface;

class QueueServiceProvider extends ServiceProviderAwesome
{

    public function register(): void
    {
        $config = $this->container->get('config')->get('queue');
        if (is_null($config)) {
            return;
        }
        $config = $config[$config['default']] ?? [];
        $this->container
            ->alias('queue', $config['driver'])
            ->alias(QueueInterface::class, $config['driver'])
            ->singleton($config['driver'], function () use ($config) {
                $class = $config['driver'];
                return new $class($config);
            });
    }

}
