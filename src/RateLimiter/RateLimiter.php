<?php

declare(strict_types=1);

namespace TinyFramework\RateLimiter;

use TinyFramework\Cache\CacheInterface;

class RateLimiter implements RateLimiterInterface
{
    public function __construct(
        protected CacheInterface $cache,
        protected string $name,
        protected int $window,
        protected int $limit
    ) {
    }

    private function buildKey(string $key): string
    {
        return sprintf('ratelimit:%s:%s', $this->name, $key);
    }

    public function consume(string $key): RateLimit
    {
        $now = time();
        $key = $this->buildKey($key);
        $window = $now - $this->window;
        $items = $this->cache->get($key);

        $items = is_array($items)
            ? array_values(array_filter($items, fn (float|int $time) => $time > $window))
            : [];
        $count = count($items);
        $isAccepted = $count < $this->limit;
        $items[] = $now;
        $this->cache->set($key, $items, $this->window);

        $index = $count + 1 - $this->limit;
        $time = $items[$index] ?? $items[0];

        return new RateLimit(
            $isAccepted,
            max(0, $this->limit - $count - 1),
            $this->limit,
            intval($time + $this->window)
        );
    }

    public function reset(string $key): RateLimiterInterface
    {
        $this->cache->forget($this->buildKey($key));
        return $this;
    }
}
