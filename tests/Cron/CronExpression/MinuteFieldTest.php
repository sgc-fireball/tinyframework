<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Cron\CronExpression;

use PHPUnit\Framework\TestCase;
use TinyFramework\Cron\CronExpression\MinuteField;

class MinuteFieldTest extends TestCase
{
    public function testWildcard()
    {
        $field = new MinuteField('*');
        $this->assertEquals(range(0, 59), $field->parse());
    }

    public function testList()
    {
        $field = new MinuteField('0,1,2,3,4');
        $this->assertEquals(range(0, 4), $field->parse());
    }

    public function testRange()
    {
        $field = new MinuteField('2-4');
        $this->assertEquals(range(2, 4), $field->parse());
    }

    public function testStep()
    {
        $field = new MinuteField('*/6');
        $this->assertEquals([0, 6, 12, 18, 24, 30, 36, 42, 48, 54], $field->parse());
    }

    public function testMixed()
    {
        $field = new MinuteField('1,6-12/2');
        $this->assertEquals([1, 6, 8, 10, 12], $field->parse());
    }
}
