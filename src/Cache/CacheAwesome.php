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

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->forget($offset);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    public function __unset($name)
    {
        $this->forget($name);
    }

    public function __isset($name)
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
        if (!is_array($tags) && !is_string($tags)) {
            throw new \InvalidArgumentException('Argument #1 must be a type of array|string.');
        }
        $class = get_class($this);
        $instance = new $class($this->config);
        $instance->tags = array_map(function (string $tag) {
            return 'tag:' . $tag;
        }, is_array($tags) ? $tags : [$tags]);
        return $instance;
    }

    public function remember(string $key, Closure $closure,null|int|\DateTime|\DateTimeInterface $ttl = null)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }
        $data = $closure();
        $this->set($key, $data, $ttl);
        return $data;
    }

}
