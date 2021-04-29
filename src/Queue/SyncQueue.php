<?php declare(strict_types=1);

namespace TinyFramework\Queue;

class SyncQueue implements QueueInterface
{

    public function push(JobInterface $job): static
    {
        try {
            $job->handle();
        } catch (\Throwable $e) {
            logger()->error(exception2text($e));
        }
        return $this;
    }

    public function name(string $name = null): static|string
    {
        if (is_null($name)) {
            return 'default';
        }
        return $this;
    }

    public function pop(int $timeout = 1): ?JobInterface
    {
        return null;
    }

    public function count(): int
    {
        return 0;
    }

}
