<?php

declare(strict_types=1);

namespace TinyFramework\Session;

use ArrayAccess;
use DateInterval;
use DateTime;
use DateTimeInterface;
use TinyFramework\Helpers\Uuid;

#[\AllowDynamicProperties]
abstract class SessionAwesome implements SessionInterface, ArrayAccess
{
    protected ?string $id = null;

    protected array $data = [];

    protected int $ttl = 300;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->data);
    }

    public function get(string $key): mixed
    {
        if (\array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return null;
    }

    public function set(string $key, mixed $value): static
    {
        if ($value === null && \array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function forget(string $key): static
    {
        if (\array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }
        return $this;
    }

    public function regenerate(bool $cleanup = false): static
    {
        $data = $this->data;
        if ($this->id) {
            $this->destroy();
        }
        $this->id = $this->newId();
        $this->data = $cleanup ? [] : $data;
        return $this;
    }

    protected function calculateExpiration(null|int|DateTimeInterface|DateInterval $ttl): int|null
    {
        if ($ttl === null) {
            return null;
        }
        if ($ttl instanceof DateInterval) {
            $ttl = (new DateTime('now'))->add($ttl);
        }
        if ($ttl instanceof DateTimeInterface) {
            return (int)$ttl->format('U');
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

    protected function newId(): string
    {
        return Uuid::v4();
    }
}
