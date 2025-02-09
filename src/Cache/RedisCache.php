<?php

declare(strict_types=1);

namespace TinyFramework\Cache;

use Redis;
use RuntimeException;

class RedisCache extends CacheAwesome
{
    /** @var Redis */
    private Redis $redis;

    private string $prefix = '';

    public function __construct(#[\SensitiveParameter] array $config = [])
    {
        parent::__construct($config);
        $this->config['host'] = $config['host'] ?? '127.0.0.1';
        $this->config['port'] = (int)($config['port'] ?? 6379);
        $this->config['password'] = $config['password'] ?? null;
        $this->config['database'] = (int)($config['database'] ?? 0);
        $this->config['read_write_timeout'] = (int)($config['read_write_timeout'] ?? -1);
        $this->config['prefix'] = trim($config['prefix'] ?? 'cache', ':') . ':';

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

    protected function calculateExpiration(null|int|\DateTimeInterface|\DateInterval $ttl): int|null
    {
        $ttl = parent::calculateExpiration($ttl);
        return $ttl === null ? null : max(0, $ttl - time());
    }

    private function addKeyToTags(string $key): static
    {
        foreach ($this->tags as $tag) {
            $insert = true;
            if ($this->redis->exists($this->prepareKey($tag))) {
                $list = $this->redis->lrange($this->prepareKey($tag), 0, -1);
                $insert = !array_key_exists($this->prepareKey($key), $list);
            }
            if ($insert) {
                $this->redis->rpush($this->prepareKey($tag), $this->prepareKey($key));
            }
        }
        return $this;
    }

    public function get(string $key): mixed
    {
        if ($this->has($key)) {
            return unserialize($this->redis->get($this->prepareKey($key))) ?? null;
        }
        return null;
    }

    public function has(string $key): bool
    {
        $result = $this->redis->exists($this->prepareKey($key));
        if (is_bool($result) && $result) {
            return true;
        }
        if (is_int($result) && $result > 0) {
            return true;
        }
        return false;
    }

    public function set(string $key, mixed $value = null, null|int|\DateTimeInterface|\DateInterval $ttl = null): static
    {
        $ttl = $this->calculateExpiration($ttl);
        if ($ttl === null) {
            $this->redis->set($this->prepareKey($key), serialize($value));
        } else {
            $this->redis->setex($this->prepareKey($key), $ttl, serialize($value));
        }
        $this->addKeyToTags($key);
        return $this;
    }

    public function forget(string $key): static
    {
        $this->redis->del($this->prepareKey($key));
        return $this;
    }

    public function clear(): static
    {
        $deleteKeys = [];
        if (\count($this->tags)) {
            foreach ($this->tags as $tag) {
                if ($this->redis->exists($this->prepareKey($tag))) {
                    $deleteKeys = array_merge($deleteKeys, $this->redis->lrange($this->prepareKey($tag), 0, -1));
                }
                $deleteKeys[] = $this->prepareKey($tag);
            }
        } else {
            $deleteKeys = $this->redis->keys($this->prepareKey('*'));
        }
        if (\count($deleteKeys)) {
            $this->redis->del($deleteKeys);
        }
        return $this;
    }

}
