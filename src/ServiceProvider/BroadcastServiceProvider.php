<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use Psr\Log\LoggerInterface;
use TinyFramework\Broadcast\BroadcastController;
use TinyFramework\Broadcast\BroadcastInterface;
use TinyFramework\Broadcast\BroadcastManager;
use TinyFramework\Http\Router;

class BroadcastServiceProvider extends ServiceProviderAwesome
{
    public function register(): void
    {
        $config = $this->container->get('config')->get('broadcast');
        if ($config === null) {
            return;
        }
        $config = $config[$config['default']] ?? [];
        $this->container->singleton(BroadcastManager::class, fn () => (new BroadcastManager())->load());
        $this->container
            ->alias('broadcast', $config['driver'])
            ->alias(BroadcastInterface::class, $config['driver'])
            ->singleton($config['driver'], function () use ($config) {
                $class = $config['driver'];
                return new $class($config);
            });
    }

    public function boot(): void
    {
        $router = $this->container->get('router');
        assert($router instanceof Router);
        $router->group(
            ['middleware' => config('broadcast.global.middleware') ?? []],
            function (Router $router) {
                $router->post('broadcast/auth', BroadcastController::class . '@auth')->name('broadcast.auth');
            }
        );
    }
}
