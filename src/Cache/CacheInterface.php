<?php

declare(strict_types=1);

namespace TinyFramework\Cache;

use Closure;

interface CacheInterface
{
    public function clear(): CacheInterface;

    public function forget(string $key): CacheInterface;

    public function get(string $key): mixed;

    public function has(string $key): bool;

    public function set(string $key, mixed $value = null, null|int|\DateTimeInterface|\DateInterval $ttl = null): CacheInterface;

    public function tag(array|string $tags): CacheInterface;

    public function remember(string $key, Closure $closure, null|int|\DateTimeInterface|\DateInterval $ttl = null): mixed;
}
