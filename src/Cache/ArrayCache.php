<?php declare(strict_types=1);

namespace TinyFramework\Cache;

class ArrayCache extends CacheAwesome
{

    private array $cache = [];

    public function clear(): static
    {
        if (count($this->tags)) {
            foreach ($this->tags as $tag) {
                $keys = $this->get($tag) ?? [];
                foreach ($keys as $key) {
                    $this->forget($key);
                }
                $this->forget($tag);
            }
        } else {
            $this->cache = [];
        }
        return $this;
    }

    public function get(string $key)
    {
        if ($this->has($key)) {
            return unserialize($this->cache[$key]['value']) ?? null;
        }
        return null;
    }

    public function has(string $key): bool
    {
        if (array_key_exists($key, $this->cache)) {
            if ($this->cache[$key]['ttl'] === null || $this->cache[$key]['ttl'] > time()) {
                return true;
            }
            unset($this->cache[$key]);
        }
        return false;
    }

    public function set(string $key, $value = null,null|int|\DateTime|\DateTimeInterface $ttl = null): static
    {
        $this->cache[$key] = [
            'value' => serialize($value),
            'ttl' => $ttl ? $this->calculateExpiration($ttl) : null
        ];
        $this->addKeyToTags($key);
        return $this;
    }

    public function forget(string $key): static
    {
        if (array_key_exists($key, $this->cache)) {
            unset($this->cache[$key]);
        }
        return $this;
    }

    private function addKeyToTags(string $key): static
    {
        foreach ($this->tags as $tag) {
            if (!array_key_exists($tag, $this->cache)) {
                $this->cache[$tag] = [];
            }
            $this->cache[$tag][$key] = 1;
        }
        return $this;
    }


}
