<?php

namespace TinyFramework\Helpers;

class DateInterval extends \DateInterval
{

    use Macroable;

    public static function wrap(\DateInterval|DateInterval|string $interval): DateInterval
    {
        if (is_string($interval)) {
            return new DateInterval($interval);
        }
        if ($interval instanceof DateInterval) {
            return $interval;
        }
        $self = new DateInterval('P0Y'); // zero
        $self->y = $interval->y;
        $self->m = $interval->m;
        $self->d = $interval->d;
        $self->h = $interval->h;
        $self->i = $interval->i;
        $self->s = $interval->s;
        $self->f = $interval->f;
        $self->invert = $interval->invert;
        $self->days = $interval->days;
        return $self;
    }

    public static function year(): DateInterval
    {
        return static::years(1);
    }

    public static function years(int $years): DateInterval
    {
        return new DateInterval('P' . $years . 'Y');
    }

    public static function month(): DateInterval
    {
        return static::months(1);
    }

    public static function months(int $months): DateInterval
    {
        return new DateInterval('P' . $months . 'M');
    }

    public static function week(): DateInterval
    {
        return static::weeks(1);
    }

    public static function weeks(int $weeks): DateInterval
    {
        return new DateInterval('P' . $weeks . 'W');
    }

    public static function day(): DateInterval
    {
        return static::days(1);
    }

    public static function days(int $days): DateInterval
    {
        return new DateInterval('P' . $days . 'D');
    }

    public static function hour(): DateInterval
    {
        return static::hours(1);
    }

    public static function hours(int $hours): DateInterval
    {
        return new DateInterval('PT' . $hours . 'H');
    }

    public static function minute(): DateInterval
    {
        return static::minutes(1);
    }

    public static function minutes(int $minutes): DateInterval
    {
        return new DateInterval('PT' . $minutes . 'M');
    }

    public static function second(): DateInterval
    {
        return static::seconds(1);
    }

    public static function seconds(int $seconds): DateInterval
    {
        return new DateInterval('PT' . $seconds . 'S');
    }

    public function addSeconds(int $second): self
    {
        $this->s += $second;
        return $this;
    }

    public function addMinutes(int $minutes): self
    {
        $this->i += $minutes;
        return $this;
    }

    public function addHours(int $hours): self
    {
        $this->h += $hours;
        return $this;
    }

    public function addDays(int $days): self
    {
        $this->d += $days;
        return $this;
    }

    public function addMonths(int $months): self
    {
        $this->m += $months;
        return $this;
    }

    public function addYears(int $years): self
    {
        $this->y += $years;
        return $this;
    }

    public function subSeconds(int $second): self
    {
        $this->s -= $second;
        return $this;
    }

    public function subMinutes(int $minutes): self
    {
        $this->i -= $minutes;
        return $this;
    }

    public function subHours(int $hours): self
    {
        $this->h -= $hours;
        return $this;
    }

    public function subDays(int $days): self
    {
        $this->d -= $days;
        return $this;
    }

    public function subMonths(int $months): self
    {
        $this->m -= $months;
        return $this;
    }

    public function subYears(int $years): self
    {
        $this->y -= $years;
        return $this;
    }

}
