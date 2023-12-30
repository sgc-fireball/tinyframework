<?php

declare(strict_types=1);

namespace TinyFramework\Session;

use Redis;
use RuntimeException;

class RedisSession extends SessionAwesome implements SessionInterface
{
    private Redis $redis;

    private array $config = [];

    public function __construct(#[\SensitiveParameter] array $config = [])
    {
        $this->config['host'] = $config['host'] ?? '127.0.0.1';
        $this->config['port'] = (int)($config['port'] ?? 6379);
        $this->config['password'] = $config['password'] ?? null;
        $this->config['database'] = (int)($config['database'] ?? 0);
        $this->config['read_write_timeout'] = (int)($config['read_write_timeout'] ?? -1);
        $this->config['prefix'] = $config['prefix'] ?? 'session:';
        $this->config['ttl'] = (int)($this->config['ttl'] ?? 300);

        $this->redis = new Redis();
        if (!$this->redis->pconnect($this->config['host'], $this->config['port'])) {
            throw new RuntimeException('Could not connect to redis');
        }
        $this->redis->auth($this->config['password']);
        $this->redis->select($this->config['database']);
        $this->redis->setOption(Redis::OPT_PREFIX, $this->config['prefix']);
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
        $this->redis->setOption(Redis::OPT_READ_TIMEOUT, $this->config['read_write_timeout']);
    }

    public function open(?string $id): static
    {
        $this->data = [];
        $this->id = $id ?: $this->newId();
        if ($this->redis->exists($this->getId())) {
            if ($value = $this->redis->get($this->getId())) {
                $this->data = unserialize($value);
            }
        }
        return $this;
    }

    public function close(): static
    {
        $this->redis->setex($this->getId(), $this->config['ttl'], serialize($this->data));
        $this->data = [];
        return $this;
    }

    public function destroy(): static
    {
        if ($this->redis->exists($this->getId())) {
            $this->redis->del($this->getId());
        }
        $this->data = [];
        return $this;
    }

    public function clear(): static
    {
        $deleteKeys = $this->redis->keys('*');
        if (\count($deleteKeys)) {
            $this->redis->del($deleteKeys);
        }
        $this->data = [];
        return $this;
    }
}
