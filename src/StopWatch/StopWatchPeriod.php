<?php declare(strict_types=1);

namespace TinyFramework\StopWatch;

class StopWatchPeriod
{

    private float $start;
    private float $end;

    public function __construct(float $start, float $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function start(): float
    {
        return $this->start;
    }

    public function end(): float
    {
        return $this->end;
    }

    public function duration(): float
    {
        return $this->end - $this->start;
    }

}
