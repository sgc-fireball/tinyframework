<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use Psr\Log\LoggerInterface;
use TinyFramework\Broadcast\BroadcastInterface;

class BroadcastServiceProvider extends ServiceProviderAwesome
{

    public function register(): void
    {
        $config = $this->container->get('config')->get('broadcast');
        if (is_null($config)) {
            return;
        }
        $config = $config[$config['default']] ?? [];
        $this->container
            ->alias('broadcast', $config['driver'])
            ->alias(BroadcastInterface::class, $config['driver'])
            ->singleton($config['driver'], function () use ($config) {
                $class = $config['driver'];
                return new $class($config);
            });
    }

}
