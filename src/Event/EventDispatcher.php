<?php

declare(strict_types=1);

namespace TinyFramework\Event;

class EventDispatcher implements EventDispatcherInterface
{
    private array $listeners = [];

    public function addListener(string $eventName, array|string|\Closure|callable $listener, int $priority = 0): static
    {
        $this->checkEventName('eventName', $eventName);
        if (!array_key_exists($eventName, $this->listeners)) {
            $this->listeners[$eventName] = [];
        }
        if (!array_key_exists($priority, $this->listeners[$eventName])) {
            $this->listeners[$eventName][$priority] = [];
        }
        $this->listeners[$eventName][$priority][] = $listener;
        return $this;
    }

    public function removeListener(string $eventName, array|string|\Closure|callable $listener): static
    {
        $this->checkEventName('eventName', $eventName);
        if (\array_key_exists($eventName, $this->listeners)) {
            foreach ($this->listeners[$eventName] as $priority => &$listeners) {
                foreach ($listeners as $index => &$storedListener) {
                    if ($listener === $storedListener) {
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
            container()->call($listener, ['event' => $event]);
            if ($event->isPropagationStopped()) {
                break;
            }
        }
        return $this;
    }

    public function getListenersForEvent(EventInterface|string $event): iterable
    {
        $result = [];
        $eventName = \is_string($event) ? $event : \get_class($event);
        $this->checkEventName('event', $eventName);
        if (\array_key_exists($eventName, $this->listeners)) {
            foreach ($this->listeners[$eventName] as $listeners) {
                foreach ($listeners as $listener) {
                    $result[] = $listener;
                }
            }
        }
        return $result;
    }

    private function checkEventName(string $field, string $eventName): void
    {
        if (!class_exists($eventName)) {
            throw new \InvalidArgumentException(
                'Invalid argument $' . $field . ' must be an existing class name and must be implement the EventInterface.'
            );
        }
        if (!in_array(EventInterface::class, class_implements($eventName))) {
            throw new \InvalidArgumentException(
                'Invalid argument $' . $field . ' must be an existing class name and must be implement the EventInterface.'
            );
        }
    }
}
