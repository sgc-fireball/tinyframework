<?php

declare(strict_types=1);

namespace TinyFramework\RateLimiter;

interface RateLimiterInterface
{
    public function consume(string $key): RateLimit;

    public function reset(string $key): RateLimiterInterface;
}
