<?php

declare(strict_types=1);

namespace TinyFramework\Session;

use RuntimeException;

class ArraySession extends SessionAwesome implements SessionInterface
{
    private static array $sessions = [];

    public function open(string $id = null): static
    {
        $this->data = [];
        $this->id = $id ?: $this->newId();
        if (!array_key_exists($this->id, self::$sessions)) {
            return $this;
        }
        $this->data = (array)unserialize(self::$sessions[$this->id]);
        return $this;
    }

    public function clear(): static
    {
        self::$sessions = [];
        $this->data = [];
        return $this;
    }

    public function count(): int
    {
        return count(self::$sessions);
    }

    public function close(): static
    {
        self::$sessions[$this->id] = serialize($this->data);
        $this->data = [];
        return $this;
    }

    public function destroy(): static
    {
        if (array_key_exists($this->id, self::$sessions)) {
            unset(self::$sessions[$this->id]);
        }
        $this->data = [];
        return $this;
    }
}
