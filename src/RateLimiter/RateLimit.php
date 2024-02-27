<?php

declare(strict_types=1);

namespace TinyFramework\RateLimiter;

class RateLimit implements RateLimitInterface
{
    public function __construct(
        readonly private bool $isAccepted,
        readonly private int $remainingTokens,
        readonly private int $limit,
        readonly private int $retryAt,
    ) {
    }

    public function isAccepted(): bool
    {
        return $this->isAccepted;
    }

    public function wait(): void
    {
        $ttl = max(0, time() - $this->getRetryAt());
        sleep($ttl);
    }

    public function getRemainingTokens(): int
    {
        return $this->remainingTokens;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getRetryAt(): int
    {
        return max(0, $this->retryAt);
    }
}
