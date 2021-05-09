<?php declare(strict_types=1);

namespace TinyFramework\Tests\Cron\CronExpression;

use PHPUnit\Framework\TestCase;
use TinyFramework\Cron\CronExpression\HourField;

class HourFieldTest extends TestCase
{

    public function testWildcard()
    {
        $field = new HourField('*');
        $this->assertEquals(range(0, 23), $field->parse());
    }

    public function testList()
    {
        $field = new HourField('0,1,2,3,4');
        $this->assertEquals(range(0, 4), $field->parse());
    }

    public function testRange()
    {
        $field = new HourField('2-4');
        $this->assertEquals(range(2, 4), $field->parse());
    }

    public function testStep()
    {
        $field = new HourField('*/6');
        $this->assertEquals([0, 6, 12, 18], $field->parse());
    }

    public function testMixed()
    {
        $field = new HourField('1,6-12/2');
        $this->assertEquals([1, 6, 8, 10, 12], $field->parse());
    }

}
