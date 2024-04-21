<?php

declare(strict_types=1);

namespace TinyFramework\Cron;

use DateTime;
use TinyFramework\Cron\CronExpression\DayOfMonthField;
use TinyFramework\Cron\CronExpression\DayOfWeekField;
use TinyFramework\Cron\CronExpression\HourField;
use TinyFramework\Cron\CronExpression\MinuteField;
use TinyFramework\Cron\CronExpression\MonthField;

class CronExpression
{
    private MinuteField $minute;

    private HourField $hour;

    private DayOfMonthField $dom;

    private MonthField $month;

    private DayOfWeekField $dow;

    public function __construct(string $expression)
    {
        $expression = $expression === '@yearly' ? '0 0 1 1 *' : $expression;
        $expression = $expression === '@annually' ? '0 0 1 1 *' : $expression;
        $expression = $expression === '@monthly' ? '0 0 1 * *' : $expression;
        $expression = $expression === '@weekly' ? '0 0 * * 0' : $expression;
        $expression = $expression === '@daily' ? '0 0 * * *' : $expression;
        $expression = $expression === '@hourly' ? '0 * * * *' : $expression;
        list($minute, $hour, $dom, $month, $dow) = explode(' ', $expression, 5);
        $this->minute = new MinuteField($minute);
        $this->hour = new HourField($hour);
        $this->dom = new DayOfMonthField($dom);
        $this->dow = new DayOfWeekField($dow);
        $this->month = new MonthField($month);
    }

    public function isDue(DateTime|\DateTimeImmutable $time = null): bool
    {
        $time ??= now();
        if (!$this->month->inExpression(\intval($time->format('m')))) {
            return false;
        }
        if (!$this->dow->inExpression(\intval($time->format('N')))) {
            return false;
        }
        if (!$this->dom->inExpression(\intval($time->format('d')))) {
            return false;
        }
        if (!$this->hour->inExpression(\intval($time->format('H')))) {
            return false;
        }
        if (!$this->minute->inExpression(\intval($time->format('i')))) {
            return false;
        }
        return true;
    }

    public function getNextRunDate(DateTime|\DateTimeImmutable $next = null): DateTime
    {
        $next ??= now();
        if ($next instanceof \DateTimeImmutable) {
            $next = new DateTime($next->format('Y-m-d H:i:00'), $next->getTimezone());
        }
        $next->modify('+1 minute');
        while (true) {
            if (!$this->month->inExpression(\intval($next->format('m')))) {
                $this->month->increment($next);
                continue;
            }
            if (!$this->dow->inExpression(\intval($next->format('N')))) {
                $this->dow->increment($next);
                continue;
            }
            if (!$this->dom->inExpression(\intval($next->format('d')))) {
                $this->dom->increment($next);
                continue;
            }
            if (!$this->hour->inExpression(\intval($next->format('H')))) {
                $this->hour->increment($next);
                continue;
            }
            if (!$this->minute->inExpression(\intval($next->format('i')))) {
                $this->minute->increment($next);
                continue;
            }
            break;
        }
        return $next;
    }

    public function getPreviousRunDate(DateTime|\DateTimeImmutable $previous = null): DateTime
    {
        $previous ??= now();
        if ($previous instanceof \DateTimeImmutable) {
            $previous = new DateTime($previous->format('Y-m-d H:i:00'), $previous->getTimezone());
        }
        $previous->modify('-1 minute');
        while (true) {
            if (!$this->month->inExpression(\intval($previous->format('m')))) {
                $this->month->decrement($previous);
                continue;
            }
            if (!$this->dow->inExpression(\intval($previous->format('N')))) {
                $this->dow->decrement($previous);
                continue;
            }
            if (!$this->dom->inExpression(\intval($previous->format('d')))) {
                $this->dom->decrement($previous);
                continue;
            }
            if (!$this->hour->inExpression(\intval($previous->format('H')))) {
                $this->hour->decrement($previous);
                continue;
            }
            if (!$this->minute->inExpression(\intval($previous->format('i')))) {
                $this->minute->decrement($previous);
                continue;
            }
            break;
        }
        return $previous;
    }
}
