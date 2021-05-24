<?php declare(strict_types=1);

namespace TinyFramework\Cache;

use RuntimeException;

class FileCache extends CacheAwesome
{

    private string $path;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->path = $this->config['path'] ?? sys_get_temp_dir();
        if (!is_dir($this->path)) {
            if (!mkdir($this->path, 0770, true)) {
                throw new RuntimeException('Could not create cache folder.');
            }
        }
        if (!is_readable($this->path) || !is_writable($this->path)) {
            throw new RuntimeException('Invalid cache folder permission.');
        }
    }

    private function key2file(string $key): string
    {
        return sprintf('%s/%s.cache.tmp', $this->path, hash('sha3-256', $key));
    }

    public function clear(): static
    {
        if (count($this->tags)) {
            foreach ($this->tags as $tag) {
                $keys = $this->get($tag) ?? [];
                foreach ($keys as $key => $item) {
                    $this->forget($key);
                }
                $this->forget($tag);
            }
        } else {
            foreach (glob($this->path . '/*.cache.tmp') as $item) {
                unlink($item);
            }
        }
        return $this;
    }

    public function get(string $key): mixed
    {
        if ($this->has($key)) {
            return unserialize(file_get_contents($this->key2file($key))) ?? null;
        }
        return null;
    }

    public function has(string $key): bool
    {
        $file = $this->key2file($key);
        if (!file_exists($file)) {
            return false;
        }
        if (filemtime($file) <= time()) {
            @unlink($file);
            return false;
        }
        return true;
    }

    public function set(string $key, mixed $value = null, null|int|\DateTime|\DateTimeInterface $ttl = null): static
    {
        $file = $this->key2file($key);
        if (file_put_contents($file, serialize($value)) === false) {
            throw new RuntimeException('Could not write cache.');
        }
        if (!touch($file, $ttl ? $this->calculateExpiration($ttl) : time() + 60 * 60 * 24 * 7 * 52)) {
            throw new RuntimeException('Could set cache ttl.');
        }
        $this->addKeyToTags($key);
        return $this;
    }

    public function forget(string $key): static
    {
        $file = $this->key2file($key);
        if (!file_exists($file)) {
            return $this;
        }
        if (@unlink($key)) {
            return $this;
        }
        throw new RuntimeException('Could not clear cache key.');
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
