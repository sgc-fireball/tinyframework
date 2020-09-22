<?php declare(strict_types=1);

namespace TinyFramework\Queue;

interface QueueInterface
{

    public function name(string $name = null);

    public function count(): int;

    public function push(JobInterface $job): QueueInterface;

    public function pop(int $timeout = 1): ?JobInterface;

}
