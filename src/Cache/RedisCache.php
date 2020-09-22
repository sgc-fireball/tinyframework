<?php declare(strict_types=1);

namespace TinyFramework\Cache;

use Redis;
use Predis\Client as Predis;

class RedisCache extends CacheAwesome
{

    /** @var Predis|Redis */
    private $redis;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->config['scheme'] = $config['scheme'] ?? 'tcp';
        $this->config['host'] = $config['host'] ?? '127.0.0.1';
        $this->config['port'] = (int)($config['port'] ?? 6379);
        $this->config['password'] = $config['password'] ?? null;
        $this->config['database'] = (int)($config['database'] ?? 0);
        $this->config['timeout'] = max(1, (int)($config['timeout'] ?? 1));
        $this->config['read_write_timeout'] = (int)($config['read_write_timeout'] ?? -1);
        $this->config['profile'] = $config['profile'] ?? '2.6';
        $this->config['prefix'] = $config['prefix'] ?? 'queue:';

        if (class_exists(Redis::class)) {
            $this->redis = new Redis();
            if (!$this->redis->pconnect($this->config['host'], $this->config['port'])) {
                throw new \RuntimeException('Could not connect to redis');
            }
            $this->redis->select($this->config['database']);
            $this->redis->setOption(Redis::OPT_PREFIX, $this->config['prefix']);
            $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
            $this->redis->setOption(Redis::OPT_READ_TIMEOUT, $this->config['read_write_timeout']);
        } else {
            $this->redis = new Predis(
                [
                    'scheme' => $this->config['scheme'],
                    'host' => $this->config['host'],
                    'port' => $this->config['port'],
                    'password' => $this->config['password'],
                    'database' => $this->config['database'],
                    'timeout' => $this->config['timeout'],
                    'read_write_timeout' => $this->config['read_write_timeout']
                ],
                [
                    'profile' => $this->config['profile'],
                    'prefix' => $this->config['prefix'],
                ]
            );
        }
    }

    public function clear(): CacheInterface
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

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $value = $default;
        if ($this->has($key)) {
            $value = unserialize($this->redis->get($key));
        }
        return $value ?: $default;
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($key) > 0;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param null|int|\DateTime|\DateTimeInterface $ttl
     * @return CacheInterface
     */
    public function set(string $key, $value = null, $ttl = null): CacheInterface
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

    public function forget(string $key): CacheInterface
    {
        $this->redis->del($key);
        return $this;
    }

    /**
     * @param null|int|\DateTime|\DateTimeInterface $ttl
     * @return null|int
     */
    protected function calculateExpiration($ttl): ?int
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

    private function addKeyToTags(string $key): RedisCache
    {
        foreach ($this->tags as $tag) {
            $insert = true;
            if ($this->redis->exists($tag)) {
                $list = $this->redis->lrange($tag, 0, -1);
                $insert = !array_key_exists($tag, $list);
            }
            if ($insert) {
                if ($this->redis instanceof Redis) {
                    $this->redis->rpush($tag, $key);
                } else {
                    $this->redis->rpush($tag, [$key]);
                }
            }
        }
        return $this;
    }

}
