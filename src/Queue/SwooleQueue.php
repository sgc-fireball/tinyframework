<?php

declare(strict_types=1);

namespace TinyFramework\Queue;

use Swoole\Http\Server as HttpServer;
use Swoole\Websocket\Server as WebsocketServer;
use Swoole\Timer;
use TinyFramework\Core\Container;

class SwooleQueue implements QueueInterface
{

    private HttpServer $server;

    public function __construct(Container $container)
    {
        $this->server = $container->has(WebsocketServer::class)
            ? $container->get(WebsocketServer::class) :
            $container->get(HttpServer::class);
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
            function (HttpServer $server, JobInterface $job) {
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
