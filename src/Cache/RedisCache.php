<?php declare(strict_types=1);

namespace TinyFramework\Cache;

use Redis;
use RuntimeException;

class RedisCache extends CacheAwesome
{

    /** @var Redis */
    private Redis $redis;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->config['host'] = $config['host'] ?? '127.0.0.1';
        $this->config['port'] = (int)($config['port'] ?? 6379);
        $this->config['password'] = $config['password'] ?? null;
        $this->config['database'] = (int)($config['database'] ?? 0);
        $this->config['read_write_timeout'] = (int)($config['read_write_timeout'] ?? -1);
        $this->config['prefix'] = $config['prefix'] ?? 'cache:';

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

    public function clear(): static
    {
        $deleteKeys = [];
        if (count($this->tags)) {
            foreach ($this->tags as $tag) {
                if ($this->redis->exists($tag)) {
                    $deleteKeys = array_merge($deleteKeys, $this->redis->lrange($tag, 0, -1));
                }
                $deleteKeys[] = $tag;
            }
        } else {
            $deleteKeys = $this->redis->keys('*');
        }
        if (count($deleteKeys)) {
            $this->redis->del(array_unique($deleteKeys));
        }
        return $this;
    }

    public function get(string $key): mixed
    {
        if ($this->has($key)) {
            return unserialize($this->redis->get($key)) ?? null;
        }
        return null;
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($key) > 0;
    }

    public function set(string $key, mixed $value = null, null|int|\DateTime|\DateTimeInterface $ttl = null): static
    {
        $ttl = $this->calculateExpiration($ttl);
        if (is_null($ttl)) {
            $this->redis->set($key, serialize($value));
        } else {
            $this->redis->setex($key, $ttl, serialize($value));
        }
        $this->addKeyToTags($key);
        return $this;
    }

    public function forget(string $key): static
    {
        $this->redis->del($key);
        return $this;
    }

    protected function calculateExpiration(null|int|\DateTime|\DateTimeInterface $ttl): int|null
    {
        if (is_null($ttl)) {
            return null;
        }
        if ($ttl instanceof \DateTime) {
            return max(0, (int)$ttl->format('U') - time());
        }
        if ($ttl instanceof \DateTimeInterface) {
            $ttl = $ttl->getTimestamp();
        }
        return $ttl;
    }

    private function addKeyToTags(string $key): static
    {
        foreach ($this->tags as $tag) {
            $insert = true;
            if ($this->redis->exists($tag)) {
                $list = $this->redis->lrange($tag, 0, -1);
                $insert = !array_key_exists($tag, $list);
            }
            if ($insert) {
                $this->redis->rpush($tag, $key);
            }
        }
        return $this;
    }

}
