<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\ContainerInterface;
use TinyFramework\Http\Router;

class RouterServiceProvider extends ServiceProviderAwesome
{

    public function register()
    {
        $this->container
            ->alias('router', Router::class)
            ->singleton(Router::class, function () {
                return (new Router($this->container))->load();
            });
    }

}
