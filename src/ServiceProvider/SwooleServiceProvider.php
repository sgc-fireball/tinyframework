<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\Config;
use TinyFramework\Http\HttpKernel;
use Swoole\Http\Server as HttpServer;
use Swoole\Websocket\Server as WebsocketServer;

class SwooleServiceProvider extends ServiceProviderAwesome
{
    public function register(): void
    {
        if ($this->isLoadable()) {
            $this->container->alias(HttpServer::class, WebsocketServer::class);
            $this->container->singleton(
                WebsocketServer::class,
                function (Config $config): HttpServer {
                    $config = (array)$config->get('swoole');
                    $server = new WebsocketServer(
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

    public function isLoadable(): bool
    {
        $kernel = $this->container->get('kernel');
        return defined('SWOOLE')
            && $kernel instanceof HttpKernel
            && $kernel->runningInConsole()
            && is_array($this->container->get('config')->get('swoole'))
            && extension_loaded('swoole')
            && class_exists(WebsocketServer::class);
    }
}
