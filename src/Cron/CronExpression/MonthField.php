<?php declare(strict_types=1);

namespace TinyFramework\Cron\CronExpression;

class MonthField extends AbstractField
{

    protected int $minValue = 1;

    protected int $maxValue = 12;

    public function increment(\DateTime $time): \DateTime
    {
        return $time->modify('first day of next month')->setTime(0, 0, 0, 0);
    }

    public function decrement(\DateTime $time): \DateTime
    {
        return $time->modify('last day of previous month')->setTime(23, 59, 0, 0);
    }

}
