<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\ContainerInterface;

abstract class ServiceProviderAwesome implements ServiceProviderInterface
{

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function register()
    {

    }

    public function boot()
    {

    }

}
