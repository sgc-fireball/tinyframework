<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use Swoole\Websocket\Server as BaseServer;
use TinyFramework\Core\Config;
use TinyFramework\Core\ConfigInterface;
use TinyFramework\Http\HttpKernelInterface;

class SwooleServiceProvider extends ServiceProviderAwesome
{
    public function register(): void
    {
        if ($this->isLoadable()) {
            $this->container
                ->alias(\Swoole\Server::class, BaseServer::class)
                ->alias(\Swoole\Http\Server::class, BaseServer::class)
                ->singleton(
                    BaseServer::class,
                    function (Config $config): BaseServer {
                        $config = (array)$config->get('swoole');
                        $server = new BaseServer(
                            $config['host'],
                            $config['port'],
                            $config['mode'],
                            $config['sock_type']
                        );
                        $server->set($config['settings'] ?? []);
                        return $server;
                    }
                );
        }
    }

    public function boot(): void
    {
        if ($this->isLoadable()) {
            #$this->container->get('broadcast'); // die
        }
    }

    public function isLoadable(): bool
    {
        $kernel = $this->container->get('kernel');
        $config = $this->container->get('config');
        return defined('SWOOLE')
            && SWOOLE
            && $kernel instanceof HttpKernelInterface
            && $kernel->runningInConsole()
            && $config instanceof ConfigInterface
            && is_array($config->get('swoole'))
            && extension_loaded('swoole')
            && class_exists(BaseServer::class);
    }
}
