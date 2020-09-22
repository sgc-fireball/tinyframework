<?php declare(strict_types=1);

namespace TinyFramework\Cache;

class NoCache extends CacheAwesome
{

    public function clear(): CacheInterface
    {
        return $this;
    }

    public function get(string $key, $default = null)
    {
        return $default;
    }

    public function has(string $key): bool
    {
        return false;
    }

    public function set(string $key, $value = null, $ttl = null): CacheInterface
    {
        return $this;
    }

    public function forget(string $key): CacheInterface
    {
        return $this;
    }

}
