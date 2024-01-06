<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Auth\AuthManager;

class AuthServiceProvider extends ServiceProviderAwesome
{
    public function register(): void
    {
        $this->container->alias('auth.manager', AuthManager::class)
            ->singleton(AuthManager::class, function () {
                $permissionManager = new AuthManager();
                $permissionManager->addPermissions($this->container->tagged('permission'));
                return $permissionManager;
            });
    }
}
