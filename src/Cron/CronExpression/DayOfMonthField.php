<?php declare(strict_types=1);

namespace TinyFramework\Cron\CronExpression;

use DateTime;

class DayOfMonthField extends AbstractField
{

    protected int $minValue = 1;

    protected int $maxValue = 31;

    public function increment(DateTime $time): DateTime
    {
        return $time->modify('+1 day')->setTime(0, 0);
    }

    public function decrement(DateTime $time): DateTime
    {
        return $time->modify('-1 day')->setTime(23, 59);
    }

}
