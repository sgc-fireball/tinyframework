<?php declare(strict_types=1);

namespace TinyFramework\Broadcast;

interface BroadcastInterface
{

    public function publish(string $channel, array $message): BroadcastInterface;

    public function subscribe(string|array $channel, callable $callback): BroadcastInterface;

    public function psubscribe(string|array $channel, callable $callback): BroadcastInterface;

}
