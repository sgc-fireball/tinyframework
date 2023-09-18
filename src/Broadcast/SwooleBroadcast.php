<?php

declare(strict_types=1);

namespace TinyFramework\Broadcast;

use Swoole\WebSocket\Server as BaseServer;

class SwooleBroadcast implements BroadcastInterface
{
    private BaseServer $server;

    private BroadcastChannelTable $broadcastChannelTable;

    public function __construct(
        array $config,
        BaseServer $server,
        BroadcastChannelTable $broadcastChannelTable
    ) {
        if (!\extension_loaded('swoole')) {
            throw new \RuntimeException(
                sprintf(
                    'You cannot use the "%s" as the "swoole" extension is not installed.',
                    __CLASS__
                )
            );
        }
        $this->server = $server;
        $this->broadcastChannelTable = $broadcastChannelTable;
    }

    public function publish(string $channel, array $message): static
    {
        $message = json_encode([
            'type' => 'broadcast',
            'channel' => $channel,
            'payload' => $message,
        ]);
        foreach ($this->broadcastChannelTable->getFdByChannel($channel) as $fd) {
            $this->server->send($fd, $message);
        }
        return $this;
    }

    public function subscribe(string|array $channel, callable $callback): static
    {
        return $this;
    }

    public function psubscribe(string|array $pattern, callable $callback): static
    {
        return $this;
    }
}
