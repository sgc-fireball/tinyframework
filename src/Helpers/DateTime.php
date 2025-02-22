<?php

namespace TinyFramework\Helpers;

use DateTimeInterface;
use DateTimeZone;
use JsonSerializable;
use Stringable;
use Throwable;

class DateTime extends \DateTime implements JsonSerializable, Stringable, DateTimeInterface
{

    use Macroable;

    static string $defaultTimeZone = 'UTC';

    static private DateTime|null $fakeNow = null;

    public static function setDefaultTimeZone(string $timezone = 'UTC'): void
    {
        self::$defaultTimeZone = $timezone;
    }

    public static function setFakeNow(DateTime $fakeNow): void
    {
        self::$fakeNow = $fakeNow;
    }

    public static function clearFakeNow(): void
    {
        self::$fakeNow = null;
    }

    /**
     * @throws Throwable
     */
    public static function now(DateTimeZone|null $timezone = null): DateTime
    {
        if (self::$fakeNow) {
            return self::$fakeNow->clone()->setTimezone($timezone ?? new DateTimeZone(self::$defaultTimeZone));
        }
        return new DateTime('now', $timezone);
    }

    public function __construct(
        string $datetime = 'now',
        DateTimeZone|null $timezone = null
    ) {
        if ($datetime === 'now' && self::$fakeNow instanceof DateTime) {
            $datetime = self::$fakeNow->__toString();
        }
        parent::__construct($datetime, $timezone ?? new DateTimeZone(self::$defaultTimeZone));
    }

    public function getYear(): int
    {
        return (int)$this->format('Y');
    }

    public function getMonth(): int
    {
        return (int)$this->format('n');
    }

    public function getDay(): int
    {
        return (int)$this->format('d');
    }

    public function getHour(): int
    {
        return (int)$this->format('G');
    }

    public function getMinute(): int
    {
        return (int)$this->format('i');
    }

    public function getSeconds(): int
    {
        return (int)$this->format('s');
    }

    public function getMicroseconds(): int
    {
        return (int)$this->format('u');
    }

    public function getDayOfWeek(): int
    {
        return (int)$this->format('w');
    }

    public function getDayOfWeekIso8601(): int
    {
        return (int)$this->format('N');
    }

    public function getDayOfMonth(): int
    {
        return (int)$this->format('j');
    }

    public function getDaysOfMonth(): int
    {
        return (int)$this->format('t');
    }

    public function getDayOfYear(): int
    {
        return (int)$this->format('z');
    }

    public function getWeekOfYear(): int
    {
        return (int)$this->format('W');
    }

    public function setYear(int $year): self
    {
        $this->setDate($year, $this->getMonth(), $this->getDay());
        return $this;
    }

    public function setMonth(int $month): self
    {
        $this->setDate($this->getYear(), $month, $this->getDay());
        return $this;
    }

    public function setDay(int $day): self
    {
        $this->setDate($this->getYear(), $this->getMonth(), $day);
        return $this;
    }

    public function setHour(int $hour): self
    {
        $this->setTime($hour, $this->getMinute(), $this->getSeconds());
        return $this;
    }

    public function setMinute(int $minute): self
    {
        $this->setTime($this->getHour(), $minute, $this->getSeconds());
        return $this;
    }

    public function setSeconds(int $seconds): self
    {
        $this->setTime($this->getHour(), $this->getMinute(), $seconds);
        return $this;
    }

    public function setMicroseconds(int $microseconds): self
    {
        $this->setTime($this->getHour(), $this->getMinute(), $this->getSeconds(), $microseconds);
        return $this;
    }

    public function startOfYear(): self
    {
        $this->setTime(0, 0, 0, 0);
        $this->setDate($this->getYear(), 1, 1);
        return $this;
    }

    public function endOfYear(): self
    {
        $this->setTime(23, 59, 59, 999999);
        $this->setDate($this->getYear(), 12, 31);
        return $this;
    }

    public function startOfMonth(): self
    {
        $this->setTime(0, 0, 0, 0);
        $this->setDate($this->getYear(), $this->getMonth(), 1);
        return $this;
    }

    public function endOfMonth(): self
    {
        $this->setTime(23, 59, 59, 999999);
        $this->setDate($this->getYear(), $this->getMonth(), $this->format('t'));
        return $this;
    }

    public function startOfDay(): self
    {
        $this->setTime(0, 0, 0, 0);
        return $this;
    }

    public function endOfDay(): self
    {
        $this->setTime(23, 59, 59, 999999);
        return $this;
    }

    public function startOfHour(): self
    {
        $this->setTime($this->getHour(), 0, 0, 0);
        return $this;
    }

    public function endOfHour(): self
    {
        $this->setTime($this->getHour(), 59, 59, 999999);
        return $this;
    }

    public function startOfMinute(): self
    {
        $this->setTime($this->getHour(), $this->getMinute(), 0, 0);
        return $this;
    }

    public function endOfMinute(): self
    {
        $this->setTime($this->getHour(), $this->getMinute(), 59, 999999);
        return $this;
    }

    public function startOfSecond(): self
    {
        $this->setTime($this->getHour(), $this->getMinute(), $this->getSeconds(), 0);
        return $this;
    }

    public function endOfSecond(): self
    {
        $this->setTime($this->getHour(), $this->getMinute(), $this->getSeconds(), 999999);
        return $this;
    }

    public function isLeapYear(): bool
    {
        return $this->format('L') == 1;
    }

    public static function easter(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return new DateTime(date('Y-m-d 00:00:00', easter_date($year)), $timezone);
    }

    public static function shroveMonday(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return self::easter($year, $timezone)->subDay(48);
    }

    public static function shroveTuesday(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return self::easter($year, $timezone)->subDay(47);
    }

    public static function ashWednesday(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return self::easter($year, $timezone)->subDay(46);
    }

    public static function palmSunday(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return self::easter($year, $timezone)->subDay(7);
    }

    public static function maundyThursday(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return self::easter($year, $timezone)->subDay(3);
    }

    public static function goodFriday(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return self::easter($year, $timezone)->subDay(2);
    }

    public static function holySaturday(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return self::easter($year, $timezone)->subDay(1);
    }

    public static function easterMonday(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return self::easter($year, $timezone)->addDays(1);
    }

    public static function ascensionDay(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return self::easter($year, $timezone)->addDays(39);
    }

    public static function whitSunday(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return self::easter($year, $timezone)->addDays(49);
    }

    public static function whitMonday(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return self::easter($year, $timezone)->addDays(50);
    }

    public static function trinitySunday(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return self::easter($year, $timezone)->addDays(56);
    }

    public static function corpusChristi(int|null $year = null, DateTimeZone|null $timezone = null): DateTime
    {
        return self::easter($year, $timezone)->addDays(60);
    }

    public function addSecond(int $second = 1): self
    {
        return $this->addSeconds($second);
    }

    public function addSeconds(int $seconds): self
    {
        $this->modify('+' . $seconds . ' seconds');
        return $this;
    }

    public function addMinute(int $minute = 1): self
    {
        return $this->addMinutes($minute);
    }

    public function addMinutes(int $minutes): self
    {
        $this->modify('+' . $minutes . ' minutes');
        return $this;
    }

    public function addHour(int $hour = 1): self
    {
        return $this->addHours($hour);
    }

    public function addHours(int $hours): self
    {
        $this->modify('+' . $hours . ' hour');
        return $this;
    }

    public function addDay(int $day = 1): self
    {
        return $this->addDays($day);
    }

    public function addDays(int $day): self
    {
        $this->modify('+' . $day . ' day');
        return $this;
    }

    public function addMonth(int $month = 1): self
    {
        return $this->addMonths($month);
    }

    public function addMonths(int $months): self
    {
        $this->modify('+' . $months . ' month');
        return $this;
    }

    public function addYear(int $year = 1): self
    {
        return $this->addYears($year);
    }

    public function addYears(int $years): self
    {
        $this->modify('+' . $years . ' year');
        return $this;
    }

    public function subSecond(int $second = 1): self
    {
        return $this->subSeconds($second);
    }

    public function subSeconds(int $seconds): self
    {
        $this->modify('-' . $seconds . ' seconds');
        return $this;
    }

    public function subMinute(int $minute = 1): self
    {
        return $this->subMinutes($minute);
    }

    public function subMinutes(int $minutes): self
    {
        $this->modify('-' . $minutes . ' minutes');
        return $this;
    }

    public function subHour(int $hour = 1): self
    {
        return $this->subHours($hour);
    }

    public function subHours(int $hours): self
    {
        $this->modify('-' . $hours . ' hour');
        return $this;
    }

    public function subDay(int $day = 1): self
    {
        return $this->subDays($day);
    }

    public function subDays(int $day): self
    {
        $this->modify('-' . $day . ' day');
        return $this;
    }

    public function subMonth(int $month = 1): self
    {
        return $this->subMonths($month);
    }

    public function subMonths(int $months): self
    {
        $this->modify('-' . $months . ' month');
        return $this;
    }

    public function subYear(int $year = 1): self
    {
        return $this->subYears($year);
    }

    public function subYears(int $years): self
    {
        $this->modify('-' . $years . ' year');
        return $this;
    }

    public function toAtomString(): string
    {
        return $this->format(self::ATOM);
    }

    public function toCookieString(): string
    {
        return $this->format(self::COOKIE);
    }

    public function toIso8601String(): string
    {
        return $this->format('c');
    }

    public function toIso8601ExpandedString(): string
    {
        return $this->format(self::ISO8601_EXPANDED);
    }

    public function toRfc822String(): string
    {
        return $this->format(self::RFC822);
    }

    public function toRfc850String(): string
    {
        return $this->format(self::RFC850);
    }

    public function toRfc1036String(): string
    {
        return $this->format(self::RFC1036);
    }

    public function toRfc1123String(): string
    {
        return $this->format(self::RFC1123);
    }

    public function toRfc2822String(): string
    {
        return $this->format(self::RFC2822);
    }

    public function toRfc5322String(): string
    {
        return $this->format('r');
    }

    public function toRfc3339String(): string
    {
        return $this->format(self::RFC3339);
    }

    public function toRfc3339ExtendedString(): string
    {
        return $this->format(self::RFC3339_EXTENDED);
    }

    public function toRfc7231String(): string
    {
        return $this->format(self::RFC7231);
    }

    public function toRssString(): string
    {
        return $this->format(self::RSS);
    }

    public function toW3cString(): string
    {
        return $this->format(self::W3C);
    }

    public function toTimestamp(): int
    {
        return $this->format('U');
    }

    public function toFloatTimestamp(): float
    {
        return (float)$this->format('U.u');
    }

    function jsonSerialize()
    {
        return $this->format('Y-m-d\TH:i:s.up');
    }

    public function __toString(): string
    {
        return $this->format('Y-m-d\TH:i:s.up');
    }

    public function equalTo(DateTime $dateTime): bool
    {
        return $this->toTimestamp() === $dateTime->toTimestamp();
    }

    public function notEqualTo(DateTime $dateTime): bool
    {
        return $this->toTimestamp() !== $dateTime->toTimestamp();
    }

    public function greaterThan(DateTime $dateTime): bool
    {
        return $this->toTimestamp() > $dateTime->toTimestamp();
    }

    public function greaterThanOrEqualTo(DateTime $dateTime): bool
    {
        return $this->toTimestamp() >= $dateTime->toTimestamp();
    }

    public function lessThan(DateTime $dateTime): bool
    {
        return $this->toTimestamp() < $dateTime->toTimestamp();
    }

    public function lessThanOrEqualTo(DateTime $dateTime): bool
    {
        return $this->toTimestamp() <= $dateTime->toTimestamp();
    }

    public function between(DateTime $dateTimeA, DateTime $dateTimeB): bool
    {
        return $dateTimeA->toTimestamp() <= $this->toTimestamp() && $this->toTimestamp() <= $dateTimeB->toTimestamp();
    }

    public function min(DateTime $dateTime): static
    {
        return $this->toTimestamp() < $dateTime->toTimestamp() ? $this->clone() : $dateTime->clone();
    }

    public function max(DateTime $dateTime): self
    {
        return $this->toTimestamp() > $dateTime->toTimestamp() ? $this->clone() : $dateTime->clone();
    }

    public function isFuture(): bool
    {
        return $this->toTimestamp() > time();
    }

    public function isPast(): bool
    {
        return $this->toTimestamp() < time();
    }

    public function isWeekday(): bool
    {
        $weekday = $this->getDayOfWeek();
        return $weekday > 0 && $weekday < 6;
    }

    public function isWeekend(): bool
    {
        $weekday = $this->getDayOfWeek();
        return $weekday === 0 || $weekday === 6;
    }

    public function isMonday(): bool
    {
        return $this->getDayOfWeek() === 1;
    }

    public function isTuesday(): bool
    {
        return $this->getDayOfWeek() === 2;
    }

    public function isWednesday(): bool
    {
        return $this->getDayOfWeek() === 3;
    }

    public function isThursday(): bool
    {
        return $this->getDayOfWeek() === 4;
    }

    public function isFriday(): bool
    {
        return $this->getDayOfWeek() === 5;
    }

    public function isSaturday(): bool
    {
        return $this->getDayOfWeek() === 6;
    }

    public function isSunday(): bool
    {
        return $this->getDayOfWeek() === 0;
    }

    public function clone(): static
    {
        return clone $this;
    }

    public function diff(DateTimeInterface $targetObject, bool $absolute = false): DateInterval|false
    {
        $di = parent::diff($targetObject, $absolute);
        if ($di === false) {
            return false;
        }
        return DateInterval::wrap($di);
    }

    public function diffInSeconds(DateTime $dateTime, bool $absolute = false): int
    {
        $diff = $this->diff($dateTime, $absolute);
        $result = $diff->y * 365 * 24 * 60 * 60
            + $diff->m * 30 * 24 * 60 * 60
            + $diff->d * 24 * 60 * 60
            + $diff->h * 60 * 60
            + $diff->m * 60
            + $diff->s;
        return !$absolute && $this->lessThan($dateTime) ? -1 * $result : $result;
    }

    public function diffInMinutes(DateTime $dateTime, bool $absolute = false): int
    {
        $diff = $this->diff($dateTime, $absolute);
        $result = $diff->y * 365 * 24 * 60
            + $diff->m * 30 * 24 * 60
            + $diff->d * 24 * 60
            + $diff->h * 60
            + $diff->i;
        return !$absolute && $this->lessThan($dateTime) ? -1 * $result : $result;
    }

    public function diffInHours(DateTime $dateTime, bool $absolute = false): int
    {
        $diff = $this->diff($dateTime, $absolute);
        $result = $diff->y * 365 * 24
            + $diff->m * 30 * 24
            + $diff->d * 24
            + $diff->h;
        return !$absolute && $this->lessThan($dateTime) ? -1 * $result : $result;
    }

    public function diffInDays(DateTime $dateTime, bool $absolute = false): int
    {
        $diff = $this->toTimestamp() - $dateTime->toTimestamp();
        $result = abs(round($diff / 86400));
        return !$absolute && $this->lessThan($dateTime) ? -1 * $result : $result;
    }

    public function diffInWeeks(DateTime $dateTime, bool $absolute = false): int
    {
        return (int)($this->diffInDays($dateTime, $absolute) / 7);
    }

    public function diffInMonths(DateTime $dateTime, bool $absolute = false): int
    {
        $diff = $this->diff($dateTime, $absolute);
        $result = $diff->y * 12
            + $diff->m;
        return !$absolute && $this->lessThan($dateTime) ? -1 * $result : $result;
    }

    public function diffInYears(DateTime $dateTime, bool $absolute = false): int
    {
        $result = $this->diff($dateTime, $absolute)->y;
        return !$absolute && $this->lessThan($dateTime) ? -1 * $result : $result;
    }

    public function diffForHumans(DateTime $dateTime, bool $absolute = false): string
    {
        $count = 0;
        $result = '';
        $diff = $this->diff($dateTime, $absolute);
        if ($diff->y > 1) {
            $count++;
            $result = trim(sprintf('%s %dy', $result, $diff->y));
        }
        if ($diff->m > 1 && $count < 3) {
            $count++;
            $result = trim(sprintf('%s %dm', $result, $diff->m));
        }
        if ($diff->d > 1 && $count < 3) {
            $count++;
            $result = trim(sprintf('%s %dd', $result, $diff->d));
        }
        if ($diff->h > 1 && $count < 3) {
            $count++;
            $result = trim(sprintf('%s %dh', $result, $diff->h));
        }
        if ($diff->i > 1 && $count < 3) {
            $count++;
            $result = trim(sprintf('%s %di', $result, $diff->i));
        }
        if ($diff->s > 1 && $count < 3) {
            $result = trim(sprintf('%s %ds', $result, $diff->s));
        }
        if ($result) {
            return $absolute ? $result : ($this->lessThan($dateTime) ? '-' : '') . $result;
        }
        return 'just now';
    }

}
