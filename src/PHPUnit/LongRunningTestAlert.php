<?php

declare(strict_types=1);

namespace TinyFramework\PHPUnit;

use PHPUnit\Runner\AfterTestHook;

/**
 * @internal
 */
class LongRunningTestAlert implements AfterTestHook
{
    protected const MAX_SECONDS_ALLOWED = 1;

    public function executeAfterTest(string $test, float $time): void
    {
        if ($time > self::MAX_SECONDS_ALLOWED) {
            fwrite(STDERR, sprintf("\nThe %s test took %s seconds!\n", $test, $time));
        }
    }
}
