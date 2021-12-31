<?php declare(strict_types=1);

namespace TinyFramework\Queue;

abstract class JobAwesome implements JobInterface
{

    protected int $tryCount = 1;

    protected int $attempts = 1; // times

    protected int $delay = 0; // seconds

    protected string $queue = 'default';

    protected array $metadata = [];

    /**
     * @return static|mixed
     */
    public function metadata(string $name, mixed $value = null): mixed
    {
        if ($value === null) {
            return $this->metadata[$name] ?? null;
        }
        $this->metadata[$name] = $value;
        return $this;
    }

    public function attempts(int $attempts = null): static|int
    {
        if ($attempts === null) {
            return $this->attempts;
        }
        $this->attempts = $attempts;
        return $this;
    }

    public function retryAfter(): int
    {
        return max(5, $this->tryCount * 60);
    }

    public function delay(null|int|\DateTimeInterface|\DateInterval $delay = null): static|int
    {
        if ($delay === null) {
            return $this->delay; // return seconds
        }
        if ($delay instanceof \DateInterval) {
            $delay = (new \DateTime('now'))->add($delay)->getTimestamp() - time(); // into seconds
        }
        if ($delay instanceof \DateTimeInterface) {
            $delay = $delay->getTimestamp() - time();
        }
        $this->delay = (int)max(0, (int)$delay);
        return $this;
    }

    public function queue(string $queue = null): static|string
    {
        if ($queue === null) {
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
            $attempts = (int)$this->attempts();
            if ($this->tryCount < $attempts) {
                $retryAfter = $this->retryAfter();
                logger()->warning(
                    'Job {class} failed ({try}/{attempts}). Retry in {retryAfter} sec. {exception}',
                    [
                        'class' => static::class,
                        'try' => $this->tryCount,
                        'attempts' => $attempts,
                        'retryAfter' => $retryAfter,
                        'exception' => exception2text($e),
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
                    'exception' => exception2text($e),
                ]
            );
        }
    }

    abstract public function tryHandle(): void;

}
