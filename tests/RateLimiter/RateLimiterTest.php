<?php

declare(strict_types=1);

namespace TinyFramework\Tests\RateLimiter;

use PHPUnit\Framework\TestCase;
use TinyFramework\Cache\ArrayCache;
use TinyFramework\RateLimiter\RateLimit;
use TinyFramework\RateLimiter\RateLimiter;

class RateLimiterTest extends TestCase
{
    private ArrayCache $cache;

    public function setUp(): void
    {
        parent::setUp();
        $this->cache = new ArrayCache();
    }

    public function testAccepted(): void
    {
        $rateLimiter = new RateLimiter($this->cache, __METHOD__, 60, 1);
        $rateLimit = $rateLimiter->consume('test');
        $this->assertTrue($rateLimit->isAccepted());
        $this->assertEquals(1, $rateLimit->getLimit());
        $this->assertGreaterThanOrEqual(time() + 59, $rateLimit->getRetryAt()); // 1 sec. grace period
    }

    public function testReachLimit(): void
    {
        $rateLimiter = new RateLimiter($this->cache, __METHOD__, 60, 1);
        $rateLimit = $rateLimiter->consume('test');
        $this->assertTrue($rateLimit->isAccepted());
        $this->assertEquals(0, $rateLimit->getRemainingTokens());
        $this->assertEquals(1, $rateLimit->getLimit());
        $this->assertGreaterThanOrEqual(time() + 59, $rateLimit->getRetryAt()); // 1 sec. grace period

        $rateLimit = $rateLimiter->consume('test');
        $this->assertFalse($rateLimit->isAccepted());
        $this->assertEquals(0, $rateLimit->getRemainingTokens());
        $this->assertEquals(1, $rateLimit->getLimit());
        $this->assertGreaterThanOrEqual(time() + 59, $rateLimit->getRetryAt()); // 1 sec. grace period
    }

    public function testReachLimit2(): void
    {
        $rateLimiter = new RateLimiter($this->cache, __METHOD__, 60, 2);
        $rateLimit = $rateLimiter->consume('test');
        $this->assertTrue($rateLimit->isAccepted());
        $this->assertEquals(1, $rateLimit->getRemainingTokens());
        $this->assertEquals(2, $rateLimit->getLimit());
        $this->assertGreaterThanOrEqual(time() + 59, $rateLimit->getRetryAt()); // 1 sec. grace period

        $rateLimit = $rateLimiter->consume('test');
        $this->assertTrue($rateLimit->isAccepted());
        $this->assertEquals(0, $rateLimit->getRemainingTokens());
        $this->assertEquals(2, $rateLimit->getLimit());
        $this->assertGreaterThanOrEqual(time() + 59, $rateLimit->getRetryAt()); // 1 sec. grace period

        $rateLimit = $rateLimiter->consume('test');
        $this->assertFalse($rateLimit->isAccepted());
        $this->assertEquals(0, $rateLimit->getRemainingTokens());
        $this->assertEquals(2, $rateLimit->getLimit());
        $this->assertGreaterThanOrEqual(time() + 59, $rateLimit->getRetryAt()); // 1 sec. grace period

        $rateLimit = $rateLimiter->consume('test');
        $this->assertFalse($rateLimit->isAccepted());
        $this->assertEquals(0, $rateLimit->getRemainingTokens());
        $this->assertEquals(2, $rateLimit->getLimit());
        $this->assertGreaterThanOrEqual(time() + 59, $rateLimit->getRetryAt()); // 1 sec. grace period
    }

    public function testUnlock(): void
    {
        $rateLimiter = new RateLimiter($this->cache, __METHOD__, 1, 1);
        $rateLimit = $rateLimiter->consume('test');
        $this->assertTrue($rateLimit->isAccepted());
        $this->assertEquals(0, $rateLimit->getRemainingTokens());
        $this->assertEquals(1, $rateLimit->getLimit());
        $this->assertGreaterThanOrEqual(time() + 1, $rateLimit->getRetryAt()); // 1 sec. grace period

        sleep(1);

        $rateLimit = $rateLimiter->consume('test');
        $this->assertTrue($rateLimit->isAccepted());
        $this->assertEquals(0, $rateLimit->getRemainingTokens());
        $this->assertEquals(1, $rateLimit->getLimit());
        $this->assertGreaterThanOrEqual(time() + 1, $rateLimit->getRetryAt()); // 1 sec. grace period
    }

    public function testLongTest(): void
    {
        /**
         * 10 anfrage in 10 sekunden
         * - 1 sec. = 1 anfrage
         * - 1 sec. = 2 anfrage
         * - 4 sec. = 3 anfrage
         * - 4 sec. = 4 anfrage
         * - 4 sec. = 5 anfrage
         * - 5 sec. = 6 anfrage
         * - 5 sec. = 7 anfrage
         * - 5 sec. = 8 anfrage
         * - 5 sec. = 9 anfrage
         * - 5 sec. = 10 anfrage
         * - 11 sec. = 9 anfrage
         */
        $rateLimiter = new RateLimiter($this->cache, __METHOD__, 10, 10);
        $rateLimit = $rateLimiter->consume('test');
        $this->assertEquals([true, 9, 10], $this->getRateResult($rateLimit));
        $rateLimit = $rateLimiter->consume('test');
        $this->assertEquals([true, 8, 10], $this->getRateResult($rateLimit));
        sleep(4);
        $rateLimit = $rateLimiter->consume('test');
        $this->assertEquals([true, 7, 6], $this->getRateResult($rateLimit));
        $rateLimit = $rateLimiter->consume('test');
        $this->assertEquals([true, 6, 6], $this->getRateResult($rateLimit));
        $rateLimit = $rateLimiter->consume('test');
        $this->assertEquals([true, 5, 6], $this->getRateResult($rateLimit));
        sleep(1);
        $rateLimit = $rateLimiter->consume('test');
        $this->assertEquals([true, 4, 5], $this->getRateResult($rateLimit));
        $rateLimit = $rateLimiter->consume('test');
        $this->assertEquals([true, 3, 5], $this->getRateResult($rateLimit));
        $rateLimit = $rateLimiter->consume('test');
        $this->assertEquals([true, 2, 5], $this->getRateResult($rateLimit));
        $rateLimit = $rateLimiter->consume('test');
        $this->assertEquals([true, 1, 5], $this->getRateResult($rateLimit));
        $rateLimit = $rateLimiter->consume('test');
        $this->assertEquals([true, 0, 5], $this->getRateResult($rateLimit));
        sleep(6);
        $rateLimit = $rateLimiter->consume('test');
        $this->assertEquals([true, 1, 3], $this->getRateResult($rateLimit));
    }

    private function getRateResult(RateLimit $rateLimit): array
    {
        return [$rateLimit->isAccepted(), $rateLimit->getRemainingTokens(), $rateLimit->getRetryAt() - time()];
    }

}
