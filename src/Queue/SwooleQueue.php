<?php

declare(strict_types=1);

namespace TinyFramework\Queue;

use Swoole\Timer;
use Swoole\Websocket\Server as BaseServer;
use TinyFramework\Core\Container;

class SwooleQueue implements QueueInterface
{
    protected static ?Container $container = null;

    private BaseServer $server;

    public function __construct(/*array $config = []*/)
    {
        $this->server = self::$container->get(BaseServer::class);
    }

    public static function setContainer(Container $container): void
    {
        static::$container = $container;
    }

    public function name(string $name = null): QueueInterface|string
    {
        return $this;
    }

    public function count(): int
    {
        return 0;
    }

    public function push(JobInterface $job): QueueInterface
    {
        if (!$job->delay()) {
            $this->server->task($job);
            return $this;
        }
        $server = $this->server;
        Timer::after(
            $job->delay() * 1000,
            function (BaseServer $server, JobInterface $job) {
                $server->task($job);
            },
            [$server, $job]
        );
        return $this;
    }

    public function pop(): JobInterface|null
    {
        return null;
    }

    public function ack(JobInterface $job): QueueInterface
    {
        return $this;
    }

    public function nack(JobInterface $job): QueueInterface
    {
        return $this;
    }
}
