<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use TinyFramework\Helpers\Str;

class FunctionsTest extends TestCase
{

    public function testGuid(): void
    {
        $guid = [
            guid('0.00000000 0'),
            guid('0.20000000 0'),
            guid('0.40000000 0'),
            guid('0.60000000 0'),
            guid('0.80000000 0'),
            guid('0.00000000 1'),
            guid('0.20000000 1'),
            guid('0.40000000 1'),
            guid('0.60000000 1'),
            guid('0.80000000 1'),
            guid('0.00000000 ' . time()),
            guid('0.20000000 ' . time()),
            guid('0.40000000 ' . time()),
            guid('0.60000000 ' . time()),
            guid('0.80000000 ' . time()),
            guid('0.99999999 ' . time()),
        ];
        for ($i = 1; $i < count($guid) - 1; $i++) {
            $this->assertTrue($guid[$i] < $guid[$i + 1]);
        }
    }

}
