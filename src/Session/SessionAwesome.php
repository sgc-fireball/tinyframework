<?php declare(strict_types=1);

namespace TinyFramework\Session;

abstract class SessionAwesome implements SessionInterface, \ArrayAccess
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

    public function get(string $key, $default = null)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return $default;
    }

    public function set(string $key, $value): SessionInterface
    {
        if ($value === null && array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function forget(string $key): SessionInterface
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }
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
            return (int)$ttl->format('U');
        }
        if ($ttl instanceof \DateTimeInterface) {
            $ttl = $ttl->getTimestamp();
        }
        return time() + $ttl;
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }

    public function __unset($name)
    {
        $this->set($name, null);
    }

}
