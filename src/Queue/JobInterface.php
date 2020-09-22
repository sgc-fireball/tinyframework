<?php declare(strict_types=1);

namespace TinyFramework\Queue;

interface JobInterface
{

    public function attempts(int $attempts = null);

    public function retryAfter(): int;

    /**
     * @param null|int|\DateTime|\DateTimeInterface $delay
     * @return $this|int
     */
    public function delay($delay = null);

    /**
     * @param string|null $queue
     * @return $this|string
     */
    public function queue(string $queue = null);

    public function handle(): void;

}
