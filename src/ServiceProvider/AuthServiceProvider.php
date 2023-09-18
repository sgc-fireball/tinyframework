<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use Psr\Log\LoggerInterface;
use TinyFramework\Auth\AuthManager;
use TinyFramework\Broadcast\BroadcastController;
use TinyFramework\Broadcast\BroadcastInterface;
use TinyFramework\Broadcast\BroadcastManager;
use TinyFramework\Http\Router;

class AuthServiceProvider extends ServiceProviderAwesome
{
    public function register(): void
    {
        $config = $this->container->get('config')->get('auth');
        if ($config === null) {
            return;
        }
        $this->container->alias('auth.manager', AuthManager::class)
            ->singleton(AuthManager::class, function () use ($config) {
                $permissionManager = new AuthManager($config);
                $permissionManager->addPermissions($this->container->tagged('permission'));
                return $permissionManager;
            });
    }

    public function boot(): void
    {
    }
}
