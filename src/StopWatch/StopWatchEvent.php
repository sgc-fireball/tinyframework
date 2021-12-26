<?php declare(strict_types=1);

namespace TinyFramework\StopWatch;

class StopWatchEvent
{

    private float $origin;

    private string $category;

    /** @var StopWatchPeriod */
    private array $periods = [];

    /** @var float[] */
    private array $started = [];

    public function __construct(float $origin, string $category = 'default')
    {
        $this->origin = $origin;
        $this->category = $category;
    }

    public function origin(): float
    {
        return $this->origin;
    }

    public function category(): string
    {
        return $this->category;
    }

    /**
     * @return StopWatchPeriod[]
     */
    public function periods(): array
    {
        $periods = $this->periods;
        foreach ($this->started as $started) {
            $periods[] = new StopWatchPeriod($started, microtime(true));
        }
        return $periods;
    }

    public function start(): self
    {
        $this->started[] = microtime(true);
        return $this;
    }

    public function stop(): self
    {
        if (count($this->started) === 0) {
            throw new StopWatchException('StopWatchEvent::stop() called but StopWatchEvent::start() has not been called before.');
        }
        $this->periods[] = new StopWatchPeriod(array_pop($this->started), microtime(true));
        return $this;
    }

    public function lap(): self
    {
        return $this->stop()->start();
    }

    public function startTime(): float
    {
        if (!!$this->periods[0]) {
            return $this->periods[0]->startTime();
        }
        if (!!$this->started[0]) {
            return $this->started[0];
        }
        return 0;
    }

    public function endTime(): float
    {
        if (count($this->started)) {
            return microtime(true);
        }
        $last = count($this->periods);
        return $last ? $this->periods[$last - 1] : 0;
    }

    public function duration(): float
    {
        $total = 0;
        foreach ($this->periods() as $period) {
            $total += $period->duration();
        }
        return $total;
    }

    public function addPeriod(float $start, float $end): self
    {
        $this->periods[] = new StopWatchPeriod($start - $this->origin, $end - $this->origin);
        return $this;
    }

}
