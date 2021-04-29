<?php declare(strict_types=1);

namespace TinyFramework\Event;

class EventDispatcher implements EventDispatcherInterface
{

    private array $listeners = [];

    public function addListener(string $eventName, callable $listener, int $priority = 0): static
    {
        if (!array_key_exists($eventName, $this->listeners)) {
            $this->listeners[$eventName] = [];
        }
        if (!array_key_exists($priority, $this->listeners[$eventName])) {
            $this->listeners[$eventName][$priority] = [];
        }
        $this->listeners[$eventName][$priority][] = $listener;
        return $this;
    }

    public function removeListener(string $eventName, callable $listener): static
    {
        if (array_key_exists($eventName, $this->listeners)) {
            foreach ($this->listeners[$eventName] as $priority => &$listeners) {
                foreach ($listeners as $index => &$l) {
                    if ($listener === $l) {
                        unset($this->listeners[$eventName][$priority][$index]);
                    }
                }
            }
        }
        return $this;
    }

    public function dispatch(EventInterface $event): static
    {
        $listeners = $this->getListenersForEvent($event);
        foreach ($listeners as $listener) {
            $listener($event);
            if ($event->isPropagationStopped()) {
                break;
            }
        }
        return $this;
    }

    public function getListenersForEvent(object $event): iterable
    {
        $listeners = [];
        $eventName = get_class($event);
        if (array_key_exists($eventName, $this->listeners)) {
            foreach ($this->listeners[$eventName] as $priority => &$listeners) {
                foreach ($listeners as $index => $listener) {
                    $listeners[] = $listener;
                }
            }
        }
        return $listeners;
    }

}
