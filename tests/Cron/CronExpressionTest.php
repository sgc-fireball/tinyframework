<?php declare(strict_types=1);

namespace TinyFramework\Tests\Cron;

use PHPUnit\Framework\TestCase;
use TinyFramework\Cron\CronExpression;

class CronExpressionTest extends TestCase
{

    public function testEveryMinute()
    {
        $cronExpression = new CronExpression('* * * * *');
        $this->assertTrue($cronExpression->isDue());
    }

    public function testEveryTwoMinute()
    {
        $time = new \DateTime();
        $cronExpression = new CronExpression('*/2 * * * *');
        $this->assertEquals(($time->format('i') % 2) === 0, $cronExpression->isDue($time));
    }
    
}
