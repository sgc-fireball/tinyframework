<?php

declare(strict_types=1);

namespace TinyFramework\Cron\CronExpression;

use DateTime;

class HourField extends AbstractField
{
    protected int $minValue = 0;

    protected int $maxValue = 23;

    public function increment(DateTime $time): DateTime
    {
        $time->modify('+1 hour');
        return $time->setTime(intval($time->format('H')), 0);
    }

    public function decrement(DateTime $time): DateTime
    {
        $time->modify('-1 hour');
        return $time->setTime(intval($time->format('H')), 59);
    }
}
