<?php

declare(strict_types=1);

namespace TinyFramework\Broadcast;

use Redis;
use RuntimeException;

class RedisBroadcast implements BroadcastInterface
{
    private Redis $redis;

    private array $config = [];

    private string $prefix = '';

    private \Closure|array|string|null $callback = null;

    public function __construct(#[\SensitiveParameter] array $config = [])
    {
        if (!\extension_loaded('redis')) {
            throw new \RuntimeException(
                sprintf(
                    'You cannot use the "%s" as the "redis" extension is not installed.',
                    __CLASS__
                )
            );
        }

        $this->config['host'] = $config['host'] ?? '127.0.0.1';
        $this->config['port'] = (int)($config['port'] ?? 6379);
        $this->config['password'] = $config['password'] ?? null;
        $this->config['database'] = (int)($config['database'] ?? 0);
        $this->config['read_write_timeout'] = (int)($config['read_write_timeout'] ?? -1);
        $this->config['prefix'] = trim($config['prefix'] ?? 'broadcast', ':') . ':';

        $this->redis = new Redis();
        if (!$this->redis->pconnect($this->config['host'], $this->config['port'])) {
            throw new RuntimeException('Could not connect to redis');
        }
        $this->redis->auth($this->config['password']);
        $this->redis->select($this->config['database']);
        $this->prefix = trim($this->config['prefix'] ?: '', ':');
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
        $this->redis->setOption(Redis::OPT_READ_TIMEOUT, $this->config['read_write_timeout']);
    }

    private function prepareKey(string $key): string
    {
        if ($this->prefix) {
            return $this->prefix . ':' . trim($key, ':');
        }
        return trim($key, ':');
    }

    public function publish(string $channel, array $message): static
    {
        $this->redis->publish($this->prepareKey($channel), json_encode($message));
        return $this;
    }

    public function subscribe(string|array $channel, callable $callback): static
    {
        $this->callback = $callback;
        $channels = array_map(fn(string $channel) => $this->prepareKey($channel), (array)$channel);
        $this->redis->subscribe($channels, [$this, 'onMessage']);
        return $this;
    }

    /**
     * @internal
     */
    public function onMessage(Redis $connection, string $channel, string $message): void
    {
        $callback = $this->callback;
        if (is_callable($callback)) {
            $channel = str_starts_with($channel, $this->prefix)
                ? substr($channel, strlen($this->prefix) + 1)
                : $channel;
            $callback($channel, (array)json_decode($message));
        }
    }

    public function psubscribe(string|array $channel, callable $callback): static
    {
        $this->callback = $callback;
        $channels = array_map(fn(string $channel) => $this->prepareKey($channel), (array)$channel);
        $this->redis->psubscribe($channels, [$this, 'onMessage']);
        return $this;
    }
}
