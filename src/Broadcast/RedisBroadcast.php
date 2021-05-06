<?php declare(strict_types=1);

namespace TinyFramework\Broadcast;

use Redis;

class RedisBroadcast implements BroadcastInterface
{

    private Redis $redis;

    private array $config = [];

    public function __construct(array $config = [])
    {
        $this->config['host'] = $config['host'] ?? '127.0.0.1';
        $this->config['port'] = (int)($config['port'] ?? 6379);
        $this->config['password'] = $config['password'] ?? null;
        $this->config['database'] = (int)($config['database'] ?? 0);
        $this->config['read_write_timeout'] = (int)($config['read_write_timeout'] ?? -1);
        $this->config['prefix'] = $config['prefix'] ?? 'broadcast:';

        $this->redis = new Redis();
        if (!$this->redis->pconnect($this->config['host'], $this->config['port'])) {
            throw new \RuntimeException('Could not connect to redis');
        }
        $this->redis->auth($this->config['password']);
        $this->redis->select($this->config['database']);
        $this->redis->setOption(Redis::OPT_PREFIX, $this->config['prefix']);
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
        $this->redis->setOption(Redis::OPT_READ_TIMEOUT, $this->config['read_write_timeout']);
    }

    public function publish(string $channel, array $message): static
    {
        $this->redis->publish($channel, json_encode($message));
        return $this;
    }

    public function subscribe(string|array $channel, callable $callback): static
    {
        $this->redis->subscribe(
            (array)$channel,
            function (Redis $connection, string $channel, string $message) use ($callback) {
                $callback($channel, (array)json_decode($message));
            }
        );
        return $this;
    }

    public function psubscribe(string|array $pattern, callable $callback): static
    {
        $this->redis->psubscribe(
            (array)$pattern,
            function (Redis $connection, string $channel, string $message) use ($callback) {
                $callback($channel, (array)json_decode($message));
            }
        );
        return $this;
    }

}
