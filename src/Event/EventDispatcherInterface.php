<?php declare(strict_types=1);

namespace TinyFramework\Event;

interface EventDispatcherInterface
{

    public function addListener(string $eventName, callable $listener, int $priority = 0): EventDispatcherInterface;

    public function removeListener(string $eventName, callable $listener): EventDispatcherInterface;

    public function dispatch(EventInterface $event): EventDispatcherInterface;

    public function getListenersForEvent(EventInterface|string $event): iterable;

}
