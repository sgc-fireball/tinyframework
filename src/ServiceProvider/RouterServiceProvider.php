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
            ->singleton(Router::class, function (ContainerInterface $container) {
                $router = new Router($container);
                return $router->load();
            });
    }

}
