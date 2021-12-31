<?php

declare(strict_types=1);

namespace TinyFramework\Cron\CronExpression;

use DateTime;

class DayOfWeekField extends AbstractField
{
    protected int $minValue = 0;

    protected int $maxValue = 7;

    public function increment(DateTime $time): DateTime
    {
        return $time->modify('+1 day')->setTime(0, 0);
    }

    public function decrement(DateTime $time): DateTime
    {
        return $time->modify('-1 day')->setTime(23, 59);
    }

    public function inExpression(int $value): bool
    {
        if (in_array($value, [0, 7])) {
            return parent::inExpression(0) || parent::inExpression(7);
        }
        return parent::inExpression($value);
    }

    protected function fixList(array $list): array
    {
        if (in_array(7, $list) && !in_array(0, $list)) {
            $list[] = 0;
        }
        if (in_array(0, $list) && !in_array(7, $list)) {
            $list[] = 7;
        }
        sort($list);
        $list = array_unique($list);
        return $list;
    }
}
