<?php declare(strict_types=1);

namespace TinyFramework\Queue;

abstract class JobAwesome implements JobInterface
{

    private int $tryCount = 1;

    protected int $attempts = 1; // times

    protected int $delay = 0; // seconds

    protected string $queue = 'default';

    public function attempts(int $attempts = null): static|int
    {
        if (is_null($attempts)) {
            return $this->attempts;
        }
        $this->attempts = $attempts;
        return $this;
    }

    public function retryAfter(): int
    {
        return max(5, $this->tryCount * 60);
    }

    public function delay(null|int|\DateTime|\DateTimeInterface $delay = null): static|int
    {
        if (is_null($delay)) {
            return $this->delay;
        }
        if ($delay instanceof \DateTime) {
            $delay = max(0, (int)$delay->format('U') - time());
        }
        if ($delay instanceof \DateTimeInterface) {
            $delay = $delay->getTimestamp();
        }
        $this->delay = (int)$delay;
        return $this;
    }

    public function queue(string $queue = null): static|string
    {
        if (is_null($queue)) {
            return $this->queue;
        }
        $this->queue = $queue;
        return $this;
    }

    final public function handle(): void
    {
        try {
            $this->tryHandle();
        } catch (\Throwable $e) {
            /** @var int $attempts */
            $attempts = $this->attempts();
            if ($this->tryCount < $attempts) {
                $retryAfter = $this->retryAfter();
                logger()->warning(
                    'Job {class} failed ({try}/{attempts}). Retry in {retryAfter} sec. {exception}',
                    [
                        'class' => static::class,
                        'try' => $this->tryCount,
                        'attempts' => $attempts,
                        'retryAfter' => $retryAfter,
                        'exception' => exception2text($e)
                    ]
                );
                $this->tryCount++;
                queue()->push($this->delay($retryAfter));
                return;
            }
            logger()->error(
                'Job {class} failed ({try}/{attempts}). {exception}',
                [
                    'class' => static::class,
                    'try' => $this->tryCount,
                    'attempts' => $this->attempts,
                    'exception' => exception2text($e)
                ]
            );
        }
    }

    abstract public function tryHandle(): void;

}
