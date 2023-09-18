<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Broadcast\BroadcastController;
use TinyFramework\Broadcast\BroadcastInterface;
use TinyFramework\Broadcast\BroadcastManager;
use TinyFramework\Core\ConfigInterface;
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
                return $this->container->call($class, ['config' => $config]);
            });
    }

    public function boot(): void
    {
        if (!$this->container->has(BroadcastManager::class)) {
            return;
        }

        $router = $this->container->get('router');
        assert($router instanceof Router);
        $router->group(
            ['middleware' => config('broadcast.global.middleware') ?? []],
            function (Router $router) {
                $router
                    ->post('broadcast/auth', [BroadcastController::class, 'auth'])
                    ->name('broadcast.auth');
            }
        );

        $config = $this->container->get('config');
        assert($config instanceof ConfigInterface);
        if ($config->get('broadcast')['default'] === 'swoole') {
            $router
                ->websocket('broadcast/websocket', [BroadcastController::class, 'websocket'])
                ->name('broadcast.websocket');
        }
    }
}
