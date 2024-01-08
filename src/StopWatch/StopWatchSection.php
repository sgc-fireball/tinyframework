<?php

declare(strict_types=1);

namespace TinyFramework\StopWatch;

use TinyFramework\Helpers\Uuid;

class StopWatchSection
{
    private float $origin;

    private string $id;

    /** @var StopWatchEvent[] */
    private array $events = [];

    public function __construct(float $origin, string $id)
    {
        $this->origin = $origin ?: microtime(true);
        $this->id = $id ?: Uuid::v6();
    }

    public function origin(): float
    {
        return $this->origin;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function start(string $name, string $category = 'default'): StopWatchEvent
    {
        $this->events[$name] ??= new StopWatchEvent($this->origin, $category);
        return $this->events[$name]->start();
    }

    public function stop(string $name): StopWatchEvent
    {
        if (!array_key_exists($name, $this->events)) {
            throw new StopWatchException(sprintf('Could not stop event. Event "%s" is unknown.', $name));
        }
        return $this->events[$name]->stop();
    }

    public function lap(string $name): StopWatchEvent
    {
        return $this->stop($name)->start();
    }

    public function events(): array
    {
        return $this->events;
    }

    public function addEventPeriod(
        string $name,
        string $category = 'default',
        float $start = null,
        float $end = null
    ): self {
        $this->events[$name] ??= new StopWatchEvent($this->origin, $category);
        if ($start && $end) {
            $this->events[$name]->addPeriod($start, $end);
        }
        return $this;
    }
}
