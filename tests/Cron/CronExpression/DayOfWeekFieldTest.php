<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Cron\CronExpression;

use PHPUnit\Framework\TestCase;
use TinyFramework\Cron\CronExpression\DayOfWeekField;

class DayOfWeekFieldTest extends TestCase
{
    public function testWildcard()
    {
        $field = new DayOfWeekField('*');
        $this->assertEquals(range(0, 7), $field->parse());
    }

    public function testList()
    {
        $field = new DayOfWeekField('0,1,2,3,4');
        $this->assertEquals(array_merge(range(0, 4), [7]), $field->parse());
    }

    public function testRange()
    {
        $field = new DayOfWeekField('2-4');
        $this->assertEquals(range(2, 4), $field->parse());
    }

    public function testStep()
    {
        $field = new DayOfWeekField('*/2');
        $this->assertEquals([0, 2, 4, 6, 7], $field->parse());
    }

    public function testMixed()
    {
        $field = new DayOfWeekField('1,3-5/4');
        $this->assertEquals([1, 4], $field->parse());
    }
}
