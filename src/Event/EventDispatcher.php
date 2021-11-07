<?php declare(strict_types=1);

namespace TinyFramework\Event;

class EventDispatcher implements EventDispatcherInterface
{

    private array $listeners = [];

    public function addListener(string $eventName, callable $listener, int $priority = 0): static
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

    public function removeListener(string $eventName, callable $listener): static
    {
        $this->checkEventName('eventName', $eventName);
        if (\array_key_exists($eventName, $this->listeners)) {
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

    public function getListenersForEvent(EventInterface|string $event): iterable
    {
        $listeners = [];
        $eventName = \is_string($event) ? $event : \get_class($event);
        $this->checkEventName('event', $eventName);
        if (\array_key_exists($eventName, $this->listeners)) {
            foreach ($this->listeners[$eventName] as $priority => &$listeners) {
                foreach ($listeners as $index => $listener) {
                    $listeners[] = $listener;
                }
            }
        }
        return $listeners;
    }

    private function checkEventName(string $field, string $eventName): void
    {
        if (!class_exists($eventName)) {
            throw new \InvalidArgumentException('Invalid argument $' . $field . ' must be an existing class name and must be implement the EventInterface.');
        }
        if (!in_array(EventInterface::class, class_implements($eventName))) {
            throw new \InvalidArgumentException('Invalid argument $' . $field . ' must be an existing class name and must be implement the EventInterface.');
        }
    }

}
