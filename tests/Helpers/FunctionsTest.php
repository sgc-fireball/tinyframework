<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Helpers;

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    public function testGuid(): void
    {
        $guid = [];
        for ($i = 0; $i < 10_000; $i++) {
            $guid[] = guid();
        }
        for ($i = 0; $i < count($guid) - 1; $i++) {
            $this->assertTrue($guid[$i] < $guid[$i + 1]);
        }
    }
}
