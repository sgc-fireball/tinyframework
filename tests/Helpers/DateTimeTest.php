<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use TinyFramework\Helpers\DateTime;

class DateTimeTest extends TestCase
{

    protected function setUp(): void
    {
        DateTime::setDefaultTimeZone();
        DateTime::clearFakeNow();
    }

    public function testTestFakeNow(): void
    {
        $now = new DateTime('2000-01-01 01:01:01.000000');
        DateTime::setFakeNow($now);
        $this->assertEquals($now->toTimestamp(), DateTime::now()->toTimestamp());
        $this->assertEquals(strtotime('2000-01-01T01:01:01.000000+00:00'), $now->toTimestamp());
    }

    public function testClearFakeNow(): void
    {
        DateTime::setFakeNow(new DateTime('2000-01-01 01:01:01.000001'));
        DateTime::clearFakeNow();
        $now = DateTime::now();
        $this->assertEquals(time(), $now->toTimestamp());
    }

    public function testYear(): void
    {
        DateTime::setFakeNow(new DateTime('2000-01-01 01:01:01.000001'));
        $date = DateTime::now();
        $this->assertEquals(2000, $date->getYear());
        $date->setYear(2020);
        $this->assertEquals(2020, $date->getYear());
    }

    public function testMonth(): void
    {
        DateTime::setFakeNow(new DateTime('2000-01-01 01:01:01.000001'));
        $date = DateTime::now();
        $this->assertEquals(1, $date->getMonth());
        $date->setMonth(2);
        $this->assertEquals(2, $date->getMonth());
    }

    public function testDay(): void
    {
        DateTime::setFakeNow(new DateTime('2000-01-01 01:01:01.000001'));
        $date = DateTime::now();
        $this->assertEquals(1, $date->getDay());
        $date->setDay(2);
        $this->assertEquals(2, $date->getDay());
    }

    public function testHour(): void
    {
        DateTime::setFakeNow(new DateTime('2000-01-01 01:01:01.000001'));
        $date = DateTime::now();
        $this->assertEquals(1, $date->getHour());
        $date->setHour(2);
        $this->assertEquals(2, $date->getHour());
    }

    public function testMinute(): void
    {
        DateTime::setFakeNow(new DateTime('2000-01-01 01:01:01.000001'));
        $date = DateTime::now();
        $this->assertEquals(1, $date->getMinute());
        $date->setMinute(2);
        $this->assertEquals(2, $date->getMinute());
    }

    public function testSecond(): void
    {
        DateTime::setFakeNow(new DateTime('2000-01-01 01:01:01.000001'));
        $date = DateTime::now();
        $this->assertEquals(1, $date->getSeconds());
        $date->setSeconds(2);
        $this->assertEquals(2, $date->getSeconds());
    }

    public function testMicroseconds(): void
    {
        DateTime::setFakeNow(new DateTime('2000-01-01 01:01:01.000001'));
        $date = DateTime::now();
        $this->assertEquals('000001', $date->getMicroseconds());
        $date->setMicroseconds(2);
        $this->assertEquals('000002', $date->getMicroseconds());
    }


    public function testGetDayOfWeek(): void
    {
        DateTime::setFakeNow(new DateTime('2000-12-30 12:13:14.678999'));
        $this->assertEquals(6, DateTime::now()->getDayOfWeek());
        DateTime::setFakeNow(new DateTime('2000-12-31 12:13:14.678999'));
        $this->assertEquals(0, DateTime::now()->getDayOfWeek());
    }

    public function testGetDayOfWeekIso8601(): void
    {
        DateTime::setFakeNow(new DateTime('2000-12-31 12:13:14.678999'));
        $this->assertEquals(7, DateTime::now()->getDayOfWeekIso8601());
    }

    public function testGetDayOfMonth(): void
    {
        DateTime::setFakeNow(new DateTime('2000-12-31 12:13:14.678999'));
        $this->assertEquals(31, DateTime::now()->getDayOfMonth());
    }

    public function testGetDaysOfMonth(): void
    {
        DateTime::setFakeNow(new DateTime('2000-01-28 12:13:14.678999'));
        $this->assertEquals(31, DateTime::now()->getDaysOfMonth());
        DateTime::setFakeNow(new DateTime('2000-02-28 12:13:14.678999'));
        $this->assertEquals(29, DateTime::now()->getDaysOfMonth());
        DateTime::setFakeNow(new DateTime('2000-03-28 12:13:14.678999'));
        $this->assertEquals(31, DateTime::now()->getDaysOfMonth());

        DateTime::setFakeNow(new DateTime('2001-01-28 12:13:14.678999'));
        $this->assertEquals(31, DateTime::now()->getDaysOfMonth());
        DateTime::setFakeNow(new DateTime('2001-02-28 12:13:14.678999'));
        $this->assertEquals(28, DateTime::now()->getDaysOfMonth());
        DateTime::setFakeNow(new DateTime('2001-03-28 12:13:14.678999'));
        $this->assertEquals(31, DateTime::now()->getDaysOfMonth());
    }

    public function testGetDayOfYear(): void
    {
        DateTime::setFakeNow(new DateTime('2000-12-31 12:13:14.678999'));
        $this->assertEquals(365, DateTime::now()->getDayOfYear());
        DateTime::setFakeNow(new DateTime('2001-12-31 12:13:14.678999'));
        $this->assertEquals(364, DateTime::now()->getDayOfYear());
    }

    public function testGetWeekOfYear(): void
    {
        DateTime::setFakeNow(new DateTime('2000-12-31 12:13:14.678999'));
        $this->assertEquals(52, DateTime::now()->getWeekOfYear());
        DateTime::setFakeNow(new DateTime('2001-12-31 12:13:14.678999'));
        $this->assertEquals(1, DateTime::now()->getWeekOfYear());
    }

    public function testStartOfYear(): void
    {
        DateTime::setFakeNow(new DateTime('2000-06-15 12:13:14.678999'));
        $date = DateTime::now();
        $this->assertEquals('2000-01-01T00:00:00.000000Z', $date->startOfYear()->__toString());
    }

    public function testEndOfYear(): void
    {
        DateTime::setFakeNow(new DateTime('2000-06-15 12:13:14.678999'));
        $date = DateTime::now();
        $this->assertEquals('2000-12-31T23:59:59.999999Z', $date->endOfYear()->__toString());
    }

    public function testStartOfMonth(): void
    {
        DateTime::setFakeNow(new DateTime('2000-06-15 12:13:14.678999'));
        $date = DateTime::now();
        $this->assertEquals('2000-06-01T00:00:00.000000Z', $date->startOfMonth()->__toString());
    }

    public function testEndOfMonth(): void
    {
        DateTime::setFakeNow(new DateTime('2000-06-15 12:13:14.678999'));
        $date = DateTime::now();
        $this->assertEquals('2000-06-30T23:59:59.999999Z', $date->endOfMonth()->__toString());
    }

    public function testStartOfDay(): void
    {
        DateTime::setFakeNow(new DateTime('2000-06-15 12:13:14.678999'));
        $date = DateTime::now();
        $this->assertEquals('2000-06-15T00:00:00.000000Z', $date->startOfDay()->__toString());
    }

    public function testEndOfDay(): void
    {
        DateTime::setFakeNow(new DateTime('2000-06-15 12:13:14.678999'));
        $date = DateTime::now();
        $this->assertEquals('2000-06-15T23:59:59.999999Z', $date->endOfDay()->__toString());
    }

    public function testStartOfHour(): void
    {
        DateTime::setFakeNow(new DateTime('2000-06-15 12:13:14.678999'));
        $date = DateTime::now();
        $this->assertEquals('2000-06-15T12:00:00.000000Z', $date->startOfHour()->__toString());
    }

    public function testEndOfHour(): void
    {
        DateTime::setFakeNow(new DateTime('2000-06-15 12:13:14.678999'));
        $date = DateTime::now();
        $this->assertEquals('2000-06-15T12:59:59.999999Z', $date->endOfHour()->__toString());
    }

    public function testStartOfMinute(): void
    {
        DateTime::setFakeNow(new DateTime('2000-06-15 12:13:14.678999'));
        $date = DateTime::now();
        $this->assertEquals('2000-06-15T12:13:00.000000Z', $date->startOfMinute()->__toString());
    }

    public function testEndOfMinute(): void
    {
        DateTime::setFakeNow(new DateTime('2000-06-15 12:13:14.678999'));
        $date = DateTime::now();
        $this->assertEquals('2000-06-15T12:13:59.999999Z', $date->endOfMinute()->__toString());
    }

    public function testStartOfSecond(): void
    {
        DateTime::setFakeNow(new DateTime('2000-06-15 12:13:14.678999'));
        $date = DateTime::now();
        $this->assertEquals('2000-06-15T12:13:14.000000Z', $date->startOfSecond()->__toString());
    }

    public function testEndOfSecond(): void
    {
        DateTime::setFakeNow(new DateTime('2000-06-15 12:13:14.678999'));
        $date = DateTime::now();
        $this->assertEquals('2000-06-15T12:13:14.999999Z', $date->endOfSecond()->__toString());
    }

    public function testIsLeapYear(): void
    {
        DateTime::setFakeNow(new DateTime('2000-06-15 12:13:14.678999'));
        $date = DateTime::now();
        $this->assertTrue($date->isLeapYear());
    }

    public function testEastern(): void
    {
        $this->assertEquals('2000-04-23', DateTime::easter(2000)->format('Y-m-d'));
    }

    public function testShroveMonday(): void
    {
        $this->assertEquals('2000-03-06', DateTime::shroveMonday(2000)->format('Y-m-d'));
    }

    public function testShroveTuesday(): void
    {
        $this->assertEquals('2000-03-07', DateTime::shroveTuesday(2000)->format('Y-m-d'));
    }

    public function testAshWednesday(): void
    {
        $this->assertEquals('2000-03-08', DateTime::ashWednesday(2000)->format('Y-m-d'));
    }

    public function testPalmSunday(): void
    {
        $this->assertEquals('2000-04-16', DateTime::palmSunday(2000)->format('Y-m-d'));
    }

    public function testMaundyThursday(): void
    {
        $this->assertEquals('2000-04-20', DateTime::maundyThursday(2000)->format('Y-m-d'));
    }

    public function testGoodFriday(): void
    {
        $this->assertEquals('2000-04-21', DateTime::goodFriday(2000)->format('Y-m-d'));
    }

    public function testHolySaturday(): void
    {
        $this->assertEquals('2000-04-22', DateTime::holySaturday(2000)->format('Y-m-d'));
    }

    public function testEasterMonday(): void
    {
        $this->assertEquals('2000-04-24', DateTime::easterMonday(2000)->format('Y-m-d'));
    }

    public function testAscensionDay(): void
    {
        $this->assertEquals('2000-06-01', DateTime::ascensionDay(2000)->format('Y-m-d'));
    }

    public function testWhitSunday(): void
    {
        $this->assertEquals('2000-06-11', DateTime::whitSunday(2000)->format('Y-m-d'));
    }

    public function testWhitMonday(): void
    {
        $this->assertEquals('2000-06-12', DateTime::whitMonday(2000)->format('Y-m-d'));
    }

    public function testTrinitySunday(): void
    {
        $this->assertEquals('2000-06-18', DateTime::trinitySunday(2000)->format('Y-m-d'));
    }

    public function testCorpusChristi(): void
    {
        $this->assertEquals('2000-06-22', DateTime::corpusChristi(2000)->format('Y-m-d'));
    }

    public function testAddDay(): void
    {
        $this->assertEquals(date('Y-m-d', time() + 24 * 60 * 60), DateTime::now()->addDay()->format('Y-m-d'));
    }

    public function testAddDays(): void
    {
        $this->assertEquals(date('Y-m-d', time() + 2 * 24 * 60 * 60), DateTime::now()->addDays(2)->format('Y-m-d'));
        $this->assertEquals(date('Y-m-d', time() + 3 * 24 * 60 * 60), DateTime::now()->addDays(3)->format('Y-m-d'));
    }

    public function testAddMonth(): void
    {
        $this->assertEquals(date('Y-m', time() + 30 * 24 * 60 * 60), DateTime::now()->addMonth()->format('Y-m'));
    }

    public function testAddMonths(): void
    {
        $this->assertEquals(date('Y-m', time() + 2 * 30 * 24 * 60 * 60), DateTime::now()->addMonths(2)->format('Y-m'));
        $this->assertEquals(date('Y-m', time() + 3 * 30 * 24 * 60 * 60), DateTime::now()->addMonths(3)->format('Y-m'));
    }

    public function testAddYear(): void
    {
        $this->assertEquals(date('Y', time() + 365 * 24 * 60 * 60), DateTime::now()->addYear()->format('Y'));
    }

    public function testAddYears(): void
    {
        $this->assertEquals(date('Y', time() + 2 * 365 * 24 * 60 * 60), DateTime::now()->addYears(2)->format('Y'));
        $this->assertEquals(date('Y', time() + 3 * 365 * 24 * 60 * 60), DateTime::now()->addYears(3)->format('Y'));
    }

    public function testSubDay(): void
    {
        $this->assertEquals(date('Y-m-d', time() - 24 * 60 * 60), DateTime::now()->subDay()->format('Y-m-d'));
    }

    public function testSubDays(): void
    {
        $this->assertEquals(date('Y-m-d', time() - 2 * 24 * 60 * 60), DateTime::now()->subDays(2)->format('Y-m-d'));
        $this->assertEquals(date('Y-m-d', time() - 3 * 24 * 60 * 60), DateTime::now()->subDays(3)->format('Y-m-d'));
    }

    public function testSubMonth(): void
    {
        $this->assertEquals(date('Y-m', time() - 30 * 24 * 60 * 60), DateTime::now()->subMonth()->format('Y-m'));
    }

    public function testSubMonths(): void
    {
        $this->assertEquals(date('Y-m', time() - 2 * 30 * 24 * 60 * 60), DateTime::now()->subMonths(2)->format('Y-m'));
        $this->assertEquals(date('Y-m', time() - 3 * 30 * 24 * 60 * 60), DateTime::now()->subMonths(3)->format('Y-m'));
    }

    public function testSubYear(): void
    {
        $this->assertEquals(date('Y', time() - 365 * 24 * 60 * 60), DateTime::now()->subYear()->format('Y'));
    }

    public function testSubYears(): void
    {
        $this->assertEquals(date('Y', time() - 2 * 365 * 24 * 60 * 60), DateTime::now()->subYears(2)->format('Y'));
        $this->assertEquals(date('Y', time() - 3 * 365 * 24 * 60 * 60), DateTime::now()->subYears(3)->format('Y'));
    }

    public function testToAtomString(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('2000-04-28T01:01:01+00:00', DateTime::now()->toAtomString());
    }

    public function testToCookieString(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('Friday, 28-Apr-2000 01:01:01 UTC', DateTime::now()->toCookieString());
    }

    public function testToIso8601String(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('2000-04-28T01:01:01+00:00', DateTime::now()->toIso8601String());
    }

    public function testToIso8601ExpandedString(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('+2000-04-28T01:01:01+00:00', DateTime::now()->toIso8601ExpandedString());
    }

    public function testToRfc822String(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('Fri, 28 Apr 00 01:01:01 +0000', DateTime::now()->toRfc822String());
    }

    public function testToRfc850String(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('Friday, 28-Apr-00 01:01:01 UTC', DateTime::now()->toRfc850String());
    }

    public function testToRfc1036String(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('Fri, 28 Apr 00 01:01:01 +0000', DateTime::now()->toRfc1036String());
    }

    public function testToRfc1123String(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('Fri, 28 Apr 2000 01:01:01 +0000', DateTime::now()->toRfc1123String());
    }

    public function testToRfc2822String(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('Fri, 28 Apr 2000 01:01:01 +0000', DateTime::now()->toRfc2822String());
    }

    public function testToRfc5322String(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('Fri, 28 Apr 2000 01:01:01 +0000', DateTime::now()->toRfc5322String());
    }

    public function testToRfc3339String(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('2000-04-28T01:01:01+00:00', DateTime::now()->toRfc3339String());
    }

    public function testToRfc3339ExtendedString(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('2000-04-28T01:01:01.000+00:00', DateTime::now()->toRfc3339ExtendedString());
    }

    public function testToRfc7231String(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('Fri, 28 Apr 2000 01:01:01 GMT', DateTime::now()->toRfc7231String());
    }

    public function testToRssString(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('Fri, 28 Apr 2000 01:01:01 +0000', DateTime::now()->toRssString());
    }

    public function testToW3cString(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('2000-04-28T01:01:01+00:00', DateTime::now()->toW3cString());
    }

    public function testToTimestamp(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals(956883661, DateTime::now()->toTimestamp());
    }

    public function testToFloatTimestamp(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals(956883661.000001, DateTime::now()->toFloatTimestamp());
    }

    public function testJsonSerialize(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('2000-04-28T01:01:01.000001Z', DateTime::now()->jsonSerialize());
        $this->assertEquals('"2000-04-28T01:01:01.000001Z"', json_encode(DateTime::now()));
    }

    public function testToString(): void
    {
        DateTime::setFakeNow(new DateTime('2000-04-28 01:01:01.000001'));
        $this->assertEquals('2000-04-28T01:01:01.000001Z', DateTime::now()->__toString());
        $this->assertEquals('2000-04-28T01:01:01.000001Z', (string)DateTime::now());
    }

    public function testEqualTo(): void
    {
        $date1 = new DateTime('2000-04-28 01:01:01');
        $date2 = new DateTime('2000-04-28 01:01:02');
        $this->assertFalse($date1->equalTo($date2));
        $this->assertTrue($date1->setSeconds(2)->equalTo($date2));
    }

    public function testNotEqualTo(): void
    {
        $date1 = new DateTime('2000-04-28 01:01:01');
        $date2 = new DateTime('2000-04-28 01:01:02');
        $this->assertTrue($date1->notEqualTo($date2));
        $this->assertFalse($date1->setSeconds(2)->notEqualTo($date2));
    }

    public function testGreaterThan(): void
    {
        $date1 = new DateTime('2000-04-28 01:01:01');
        $date2 = new DateTime('2000-04-28 01:01:02');
        $this->assertFalse($date1->greaterThan($date2));
        $this->assertTrue($date2->greaterThan($date1));
    }

    public function testGreaterThanOrEqualTo(): void
    {
        $date1 = new DateTime('2000-04-28 01:01:01');
        $date2 = new DateTime('2000-04-28 01:01:02');
        $date3 = new DateTime('2000-04-28 01:01:02');
        $this->assertFalse($date1->greaterThanOrEqualTo($date2));
        $this->assertTrue($date2->greaterThanOrEqualTo($date1));
        $this->assertTrue($date2->greaterThanOrEqualTo($date3));
    }

    public function testLessThan(): void
    {
        $date1 = new DateTime('2000-04-28 01:01:01');
        $date2 = new DateTime('2000-04-28 01:01:02');
        $this->assertFalse($date2->lessThan($date1));
        $this->assertTrue($date1->lessThan($date2));
    }

    public function testLessThanOrEqualTo(): void
    {
        $date1 = new DateTime('2000-04-28 01:01:01');
        $date2 = new DateTime('2000-04-28 01:01:02');
        $date3 = new DateTime('2000-04-28 01:01:01');
        $this->assertTrue($date1->lessThanOrEqualTo($date2));
        $this->assertFalse($date2->lessThanOrEqualTo($date3));
        $this->assertTrue($date1->lessThanOrEqualTo($date3));
    }

    public function testBetween(): void
    {
        $date1 = new DateTime('2000-04-28 01:01:01');
        $date2 = new DateTime('2000-04-28 01:01:02');
        $date3 = new DateTime('2000-04-28 01:01:03');
        $this->assertTrue($date2->between($date1, $date3));
    }

    public function testMin(): void
    {
        $date1 = new DateTime('2000-04-28 01:01:01');
        $date2 = new DateTime('2000-04-28 01:01:02');
        $this->assertEquals(956883661, $date1->min($date2)->toTimestamp());
    }

    public function testMax(): void
    {
        $date1 = new DateTime('2000-04-28 01:01:01');
        $date2 = new DateTime('2000-04-28 01:01:02');
        $this->assertEquals(956883662, $date1->max($date2)->toTimestamp());
    }

    public function testIsFuture(): void
    {
        $date = DateTime::now()->addDay();
        $this->assertTrue($date->isFuture());
    }

    public function testIsPast(): void
    {
        $date = DateTime::now()->subDay();
        $this->assertTrue($date->isPast());
    }

    public function testIsWeekday(): void
    {
        DateTime::setFakeNow((new DateTime())->setTimestamp(strtotime('next monday')));
        $this->assertTrue(DateTime::now()->isWeekday());
        DateTime::setFakeNow((new DateTime())->setTimestamp(strtotime('next friday')));
        $this->assertTrue(DateTime::now()->isWeekday());
    }

    public function testIsWeekend(): void
    {
        DateTime::setFakeNow((new DateTime())->setTimestamp(strtotime('next saturday')));
        $this->assertTrue(DateTime::now()->isWeekend());
        DateTime::setFakeNow((new DateTime())->setTimestamp(strtotime('next sunday')));
        $this->assertTrue(DateTime::now()->isWeekend());
    }

    public function testIsMonday(): void
    {
        DateTime::setFakeNow((new DateTime())->setTimestamp(strtotime('next monday')));
        $this->assertTrue(DateTime::now()->isMonday());
    }

    public function testIsTuesday(): void
    {
        DateTime::setFakeNow((new DateTime())->setTimestamp(strtotime('next tuesday')));
        $this->assertTrue(DateTime::now()->isTuesday());
    }

    public function testIsWednesday(): void
    {
        DateTime::setFakeNow((new DateTime())->setTimestamp(strtotime('next wednesday')));
        $this->assertTrue(DateTime::now()->isWednesday());
    }

    public function testIsThursday(): void
    {
        DateTime::setFakeNow((new DateTime())->setTimestamp(strtotime('next thursday')));
        $this->assertTrue(DateTime::now()->isThursday());
    }

    public function testIsFriday(): void
    {
        DateTime::setFakeNow((new DateTime())->setTimestamp(strtotime('next friday')));
        $this->assertTrue(DateTime::now()->isFriday());
    }

    public function testIsSaturday(): void
    {
        DateTime::setFakeNow((new DateTime())->setTimestamp(strtotime('next saturday')));
        $this->assertTrue(DateTime::now()->isSaturday());
    }

    public function testIsSunday(): void
    {
        DateTime::setFakeNow((new DateTime())->setTimestamp(strtotime('next sunday')));
        $this->assertTrue(DateTime::now()->isSunday());
    }

    public function testDiffInSeconds(): void
    {
        $date1 = new DateTime('2000-01-01 00:00:00.000000');
        $date2 = new DateTime('2000-01-01 00:00:30.000000');
        $this->assertEquals(-30, $date1->diffInSeconds($date2));
        $this->assertEquals(30, $date1->diffInSeconds($date2, true));
        $this->assertEquals(30, $date2->diffInSeconds($date1));
        $this->assertEquals(30, $date2->diffInSeconds($date1, true));
    }

    public function testDiffInMinutes(): void
    {
        $date1 = new DateTime('2000-01-01 00:00:00.000000');
        $date2 = new DateTime('2000-01-01 00:30:00.000000');
        $this->assertEquals(-30, $date1->diffInMinutes($date2));
        $this->assertEquals(30, $date1->diffInMinutes($date2, true));
        $this->assertEquals(30, $date2->diffInMinutes($date1));
        $this->assertEquals(30, $date2->diffInMinutes($date1, true));
    }

    public function testDiffInHours(): void
    {
        $date1 = new DateTime('2000-01-01 00:00:00.000000');
        $date2 = new DateTime('2000-01-01 12:00:00.000000');
        $this->assertEquals(-12, $date1->diffInHours($date2));
        $this->assertEquals(12, $date1->diffInHours($date2, true));
        $this->assertEquals(12, $date2->diffInHours($date1));
        $this->assertEquals(12, $date2->diffInHours($date1, true));
    }

    public function testDiffInDays(): void
    {
        $date1 = new DateTime('2000-01-01 00:00:00.000000');
        $date2 = new DateTime('2000-12-31 00:00:00.000000');
        $this->assertEquals(-365, $date1->diffInDays($date2));
        $this->assertEquals(365, $date1->diffInDays($date2, true));
        $this->assertEquals(365, $date2->diffInDays($date1));
        $this->assertEquals(365, $date2->diffInDays($date1, true));
        $date1 = new DateTime('2001-01-01 00:00:00.000000');
        $date2 = new DateTime('2001-12-31 00:00:00.000000');
        $this->assertEquals(-364, $date1->diffInDays($date2));
        $this->assertEquals(364, $date1->diffInDays($date2, true));
        $this->assertEquals(364, $date2->diffInDays($date1));
        $this->assertEquals(364, $date2->diffInDays($date1, true));
    }

    public function testDiffInWeeks(): void
    {
        $date1 = new DateTime('2000-01-01 00:00:00.000000');
        $date2 = new DateTime('2000-01-15 00:00:00.000000');
        $this->assertEquals(-2, $date1->diffInWeeks($date2));
        $this->assertEquals(2, $date1->diffInWeeks($date2, true));
        $this->assertEquals(2, $date2->diffInWeeks($date1));
        $this->assertEquals(2, $date2->diffInWeeks($date1, true));
    }

    public function testDiffInMonths(): void
    {
        $date1 = new DateTime('2000-01-01 00:00:00.000000');
        $date2 = new DateTime('2000-03-01 00:00:00.000000');
        $this->assertEquals(-2, $date1->diffInMonths($date2));
        $this->assertEquals(2, $date1->diffInMonths($date2, true));
        $this->assertEquals(2, $date2->diffInMonths($date1));
        $this->assertEquals(2, $date2->diffInMonths($date1, true));
    }

    public function testDiffInYears(): void
    {
        $date1 = new DateTime('2000-01-01 00:00:00.000000');
        $date2 = new DateTime('2002-01-01 00:00:00.000000');
        $this->assertEquals(-2, $date1->diffInYears($date2));
        $this->assertEquals(2, $date1->diffInYears($date2, true));
        $this->assertEquals(2, $date2->diffInYears($date1));
        $this->assertEquals(2, $date2->diffInYears($date1, true));
    }

    public function testDiffForHumans(): void
    {
        $date1 = new DateTime('2000-01-01 00:00:00.000000');
        $date2 = new DateTime('2002-01-01 00:00:00.000000');
        $this->assertEquals('-2y', $date1->diffForHumans($date2));
        $this->assertEquals('2y', $date1->diffForHumans($date2, true));
        $this->assertEquals('2y', $date2->diffForHumans($date1));
        $this->assertEquals('2y', $date2->diffForHumans($date1, true));
    }

}
