<?php

declare(strict_types=1);

namespace TinyFramework\Session;

use Redis;
use RuntimeException;

class RedisSession extends SessionAwesome implements SessionInterface
{
    private Redis $redis;

    private array $config = [];

    private string $prefix = '';

    public function __construct(#[\SensitiveParameter] array $config = [])
    {
        $this->config['host'] = $config['host'] ?? '127.0.0.1';
        $this->config['port'] = (int)($config['port'] ?? 6379);
        $this->config['password'] = $config['password'] ?? null;
        $this->config['database'] = (int)($config['database'] ?? 0);
        $this->config['read_write_timeout'] = (int)($config['read_write_timeout'] ?? -1);
        $this->config['prefix'] = trim($config['prefix'] ?? 'session', ':') . ':';
        $this->config['ttl'] = (int)($this->config['ttl'] ?? 300);

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

    public function open(?string $id): static
    {
        $this->data = [];
        $this->id = $id ?: $this->newId();
        $key = $this->prepareKey($this->getId());
        if ($this->redis->exists($key)) {
            if ($value = $this->redis->get($key)) {
                $this->data = unserialize($value);
            }
        }
        return $this;
    }

    public function close(): static
    {
        if (!$this->redis->setex($this->prepareKey($this->getId()), $this->config['ttl'], serialize($this->data))) {
            throw new \RuntimeException('Could not write session information.');
        }
        $this->data = [];
        return $this;
    }

    public function destroy(): static
    {
        $key = $this->prepareKey($this->getId());
        if ($this->redis->exists($key)) {
            $this->redis->del($key);
        }
        $this->data = [];
        return $this;
    }

    public function count(): int
    {
        return count($this->redis->keys($this->prepareKey('*')));
    }

    public function clear(): static
    {
        $deleteKeys = $this->redis->keys($this->prepareKey('*'));
        if (\count($deleteKeys)) {
            $this->redis->del($deleteKeys);
        }
        $this->data = [];
        return $this;
    }
}
