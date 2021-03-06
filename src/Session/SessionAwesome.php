<?php declare(strict_types=1);

namespace TinyFramework\Session;

use ArrayAccess;

abstract class SessionAwesome implements SessionInterface, ArrayAccess
{

    protected ?string $id = null;

    protected array $data = [];

    protected int $ttl = 300;

    public function getId(): string
    {
        if ($this->id === null) {
            $this->id = guid();
        }
        return $this->id;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key): mixed
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return null;
    }

    public function set(string $key, mixed $value): static
    {
        if ($value === null && array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function forget(string $key): static
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }
        return $this;
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

    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string)$offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set((string)$offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->set((string)$offset, null);
    }

    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    public function __unset(string $name): void
    {
        $this->set($name, null);
    }

}
