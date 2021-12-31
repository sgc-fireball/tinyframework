<?php

declare(strict_types=1);

namespace TinyFramework\Cron\CronExpression;

use DateTime;

class MinuteField extends AbstractField
{
    protected int $minValue = 0;

    protected int $maxValue = 59;

    public function increment(DateTime $time): DateTime
    {
        return $time->modify('+1 minute');
    }

    public function decrement(DateTime $time): DateTime
    {
        return $time->modify('-1 minute');
    }
}
