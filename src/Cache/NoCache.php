<?php declare(strict_types=1);

namespace TinyFramework\Cache;

class NoCache extends CacheAwesome
{

    public function clear(): CacheInterface
    {
        return $this;
    }

    public function get(string $key)
    {
        return null;
    }

    public function has(string $key): bool
    {
        return false;
    }

    public function set(string $key, $value = null,null|int|\DateTime|\DateTimeInterface $ttl = null): NoCache
    {
        return $this;
    }

    public function forget(string $key): NoCache
    {
        return $this;
    }

}
