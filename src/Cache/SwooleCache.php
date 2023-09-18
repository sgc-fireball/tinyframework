<?php

declare(strict_types=1);

namespace TinyFramework\Cache;

use Swoole\Table;

class SwooleCache extends CacheAwesome
{
    private SwooleTableCache $table;

    public function __construct(
        SwooleTableCache $table,
        array $config = []
    ) {
        $this->table = $table;
        parent::__construct($config);
    }

    public function clear(): static
    {
        if (\count($this->tags)) {
            foreach ($this->tags as $tag) {
                $keys = $this->get($tag) ?? [];
                foreach ($keys as $key => $item) {
                    $this->forget($key);
                }
                $this->forget($tag);
            }
        } else {
            $ids = [];
            foreach ($this->table as $id => $value) {
                $ids[] = $id;
            }
            foreach ($ids as $id) {
                $this->table->del($id);
            }
        }
        return $this;
    }

    public function get(string $key): mixed
    {
        if ($this->has($key)) {
            return unserialize($this->table->get($key, 'value')) ?? null;
        }
        return null;
    }

    public function has(string $key): bool
    {
        $row = $this->table->get($key);
        if (!$row) {
            return false;
        }
        if ($row['expires_at'] <= time()) {
            $this->table->del($key);
            return false;
        }
        return true;
    }

    public function set(string $key, mixed $value = null, null|int|\DateTimeInterface|\DateInterval $ttl = null): static
    {
        $expiresAt = $ttl ? $this->calculateExpiration($ttl) : time() + 60 * 60 * 24 * 7 * 52;
        $this->table->set($key, ['key' => $key, 'value' => serialize($value), 'expires_at' => $expiresAt]);
        $this->addKeyToTags($key);
        return $this;
    }

    public function forget(string $key): static
    {
        if ($this->table->exist($key)) {
            $this->table->del($key);
        }
        return $this;
    }

    private function addKeyToTags(string $key): static
    {
        foreach ($this->tags as $tag) {
            $keys = $this->get($tag) ?? [];
            if (!array_key_exists($key, $keys)) {
                $keys[$key] = 1;
                $this->set($tag, $keys);
            }
        }
        return $this;
    }
}
