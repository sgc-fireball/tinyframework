<?php

declare(strict_types=1);

namespace TinyFramework\RateLimiter;

interface RateLimitInterface
{
    public function isAccepted(): bool;

    public function wait(): void;

    public function getRemainingTokens(): int;

    public function getLimit(): int;

    public function getRetryAt(): int;
}
