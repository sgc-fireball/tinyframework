<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\ContainerInterface;
use TinyFramework\Event\EventDispatcher;
use TinyFramework\Event\EventDispatcherInterface;

class EventServiceProvider extends ServiceProviderAwesome
{

    public function register(): void
    {
        $this->container
            ->alias('event', EventDispatcher::class)
            ->alias(EventDispatcherInterface::class, EventDispatcher::class)
            ->singleton(EventDispatcher::class, function () {
                return new EventDispatcher();
            });
    }

}
