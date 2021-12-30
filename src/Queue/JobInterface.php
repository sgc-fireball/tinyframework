<?php declare(strict_types=1);

namespace TinyFramework\Queue;

interface JobInterface
{

    public function metadata(string $name, mixed $value = null)/*: JobInterface|mixed*/;

    public function attempts(int $attempts = null): JobInterface|int;

    public function retryAfter(): int;

    public function delay(null|int|\DateTimeInterface|\DateInterval $delay = null): JobInterface|int;

    public function queue(string $queue = null): JobInterface|string;

    public function handle(): void;

}
