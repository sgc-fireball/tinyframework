<?php

declare(strict_types=1);

namespace TinyFramework\StopWatch;

class StopWatch
{
    /** @var StopWatchSection[] */
    private array $sections = [];

    public function __construct(float $start = null)
    {
        $this->reset($start);
    }

    public function reset(float $start = null): static
    {
        $start = is_null($start) && defined('TINYFRAMEWORK_START') ? TINYFRAMEWORK_START : $start;
        $start = is_null($start) && array_key_exists('REQUEST_TIME_FLOAT', $_SERVER) ? $_SERVER['REQUEST_TIME_FLOAT'] : $start;
        $start = is_null($start) ? microtime(true) : $start;
        $this->sections = ['main' => new StopWatchSection($start, 'main')];
        return $this;
    }

    public function origin(): float
    {
        return $this->sections['main']->origin();
    }

    public function section(string $id): StopWatchSection
    {
        if (!array_key_exists($id, $this->sections)) {
            throw new StopWatchException(sprintf('Section "%s" is unknown.', $id));
        }
        return $this->sections[$id];
    }

    public function sections(): array
    {
        return $this->sections;
    }

    public function openSection(string $id): StopWatchSection
    {
        $this->sections['main']->start('__section__.child', 'section');
        $this->sections[$id] ??= new StopWatchSection(microtime(true), $id);
        $this->sections[$id]->start('__section__', 'default');
        return $this->sections[$id];
    }

    public function closeSection(StopWatchSection|string $id): self
    {
        $id = $id instanceof StopWatchSection ? $id->id() : $id;
        if (!array_key_exists($id, $this->sections)) {
            throw new StopWatchException(sprintf('Section "%s" is unknown.', $id));
        }
        $this->sections[$id]->stop('__section__');
        $this->sections['main']->stop('__section__.child');
        return $this;
    }

    public function start(string $name, string $category = 'default'): StopWatchEvent
    {
        return $this->sections['main']->start($name, $category);
    }

    public function lap(string $name): StopWatchEvent
    {
        return $this->sections['main']->stop($name)->start();
    }

    public function stop(string $name): StopWatchEvent
    {
        return $this->sections['main']->stop($name);
    }

    public function duration(): float
    {
        return microtime(true) - $this->origin();
    }

    public function track(string $name, \Closure $closure, string $category = 'default'): void
    {
        $this->start($name, $category);
        try {
            $closure();
        } finally {
            $this->stop($name);
        }
    }

    public function trackSection(string $id, \Closure $closure): void
    {
        $this->openSection($id);
        try {
            $closure();
        } finally {
            $this->closeSection($id);
        }
    }

}
