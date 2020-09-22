<?php declare(strict_types=1);

namespace TinyFramework\Cache;

use Closure;

interface CacheInterface
{

    public function clear(): CacheInterface;

    public function forget(string $key): CacheInterface;

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    public function has(string $key): bool;

    /**
     * @param string $key
     * @param mixed $value
     * @param null|int|\DateTime|\DateTimeInterface $ttl
     * @return CacheInterface
     */
    public function set(string $key, $value = null, $ttl = null): CacheInterface;

    public function tag($tags): CacheInterface;

    public function remember(string $key, Closure $closure, $ttl = null);

}
