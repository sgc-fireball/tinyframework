<?php declare(strict_types=1);

namespace TinyFramework\Queue;

interface QueueInterface
{

    public function name(string $name = null): QueueInterface|string;

    public function count(): int;

    public function push(JobInterface $job): QueueInterface;

    public function pop(): JobInterface|null;

    public function ack(JobInterface $job): QueueInterface;

    public function nack(JobInterface $job): QueueInterface;

}
