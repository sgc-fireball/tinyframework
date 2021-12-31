<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Cron\CronExpression;

use PHPUnit\Framework\TestCase;
use TinyFramework\Cron\CronExpression\DayOfMonthField;

class DayOfMonthFieldTest extends TestCase
{
    public function testWildcard()
    {
        $field = new DayOfMonthField('*');
        $this->assertEquals(range(1, 31), $field->parse());
    }

    public function testList()
    {
        $field = new DayOfMonthField('1,2,3,4');
        $this->assertEquals(range(1, 4), $field->parse());
    }

    public function testRange()
    {
        $field = new DayOfMonthField('2-4');
        $this->assertEquals(range(2, 4), $field->parse());
    }

    public function testStep()
    {
        $field = new DayOfMonthField('*/6');
        $this->assertEquals([6, 12, 18, 24, 30], $field->parse());
    }

    public function testMixed()
    {
        $field = new DayOfMonthField('1,6-12/2');
        $this->assertEquals([1, 6, 8, 10, 12], $field->parse());
    }
}
