<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Cron\CronExpression;

use PHPUnit\Framework\TestCase;
use TinyFramework\Cron\CronExpression\MonthField;

class MonthFieldTest extends TestCase
{
    public function testWildcard()
    {
        $field = new MonthField('*');
        $this->assertEquals(range(1, 12), $field->parse());
    }

    public function testList()
    {
        $field = new MonthField('1,2,3,4');
        $this->assertEquals(range(1, 4), $field->parse());
    }

    public function testRange()
    {
        $field = new MonthField('2-4');
        $this->assertEquals(range(2, 4), $field->parse());
    }

    public function testStep()
    {
        $field = new MonthField('*/6');
        $this->assertEquals([6, 12], $field->parse());
    }

    public function testMixed()
    {
        $field = new MonthField('1,6-12/2');
        $this->assertEquals([1, 6, 8, 10, 12], $field->parse());
    }
}
