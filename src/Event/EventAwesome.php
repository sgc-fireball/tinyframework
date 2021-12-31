<?php

declare(strict_types=1);

namespace TinyFramework\Event;

abstract class EventAwesome implements EventInterface
{
    protected bool $propagationStopped = false;

    final public function stopPropagation(): static
    {
        $this->propagationStopped = true;
        return $this;
    }

    final public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
