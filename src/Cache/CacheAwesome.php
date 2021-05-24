<?php declare(strict_types=1);

namespace TinyFramework\Cache;

use ArrayAccess;
use Closure;

abstract class CacheAwesome implements CacheInterface, ArrayAccess
{

    protected array $config;

    protected array $tags = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string)$offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string)$offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set((string)$offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->forget((string)$offset);
    }

    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function __unset(string $name): void
    {
        $this->forget($name);
    }

    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    protected function calculateExpiration(null|int|\DateTime|\DateTimeInterface $ttl): int|null
    {
        if (is_null($ttl)) {
            return null;
        }
        if ($ttl instanceof \DateTime) {
            return (int)$ttl->format('U');
        }
        if ($ttl instanceof \DateTimeInterface) {
            $ttl = $ttl->getTimestamp();
        }
        return time() + $ttl;
    }

    public function tag(array|string $tags): CacheAwesome
    {
        $class = get_class($this);
        $instance = new $class($this->config);
        $instance->tags = array_map(function (string $tag) {
            return 'tag:' . $tag;
        }, is_array($tags) ? $tags : [$tags]);
        return $instance;
    }

    public function remember(string $key, Closure $closure, null|int|\DateTime|\DateTimeInterface $ttl = null): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }
        $data = $closure();
        $this->set($key, $data, $ttl);
        return $data;
    }

}
