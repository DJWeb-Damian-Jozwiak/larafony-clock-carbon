<?php

declare(strict_types=1);

namespace Larafony\Clock\Carbon\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateInterval;
use Larafony\Clock\Carbon\CarbonClock;
use Larafony\Clock\Carbon\CarbonInstant;
use Larafony\Framework\Clock\ClockFactory;
use Larafony\Framework\Clock\Enums\TimeFormat;
use Larafony\Framework\Clock\Enums\Timezone;
use Larafony\Framework\Clock\Instant;
use Larafony\Framework\Database\ORM\Contracts\Castable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CarbonInstant::class)]
final class CarbonInstantTest extends TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow('2024-06-15 12:00:00');
        ClockFactory::freeze('2024-06-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        ClockFactory::reset();
    }

    // ========================================
    // Factory Tests
    // ========================================

    public function testFromCreatesInstantFromString(): void
    {
        $instant = CarbonInstant::from('2024-01-15 10:30:00');

        $this->assertInstanceOf(CarbonInstant::class, $instant);
        $this->assertSame('2024-01-15 10:30:00', $instant->format(TimeFormat::DATETIME));
    }

    public function testImplementsCastableInterface(): void
    {
        $this->assertTrue(is_subclass_of(CarbonInstant::class, Castable::class));
    }

    public function testParseCreatesInstant(): void
    {
        $instant = CarbonInstant::parse('2024-03-20 15:45:30');

        $this->assertSame('2024-03-20 15:45:30', $instant->format(TimeFormat::DATETIME));
    }

    public function testFromDateTimeInterface(): void
    {
        $datetime = new \DateTimeImmutable('2024-02-28 08:00:00');
        $instant = CarbonInstant::fromDateTime($datetime);

        $this->assertSame('2024-02-28 08:00:00', $instant->format(TimeFormat::DATETIME));
    }

    public function testFromInstant(): void
    {
        $baseInstant = Instant::parse('2024-04-10 16:20:00');
        $carbonInstant = CarbonInstant::fromInstant($baseInstant);

        $this->assertSame('2024-04-10 16:20:00', $carbonInstant->format(TimeFormat::DATETIME));
    }

    public function testFromTimestamp(): void
    {
        $timestamp = 1705321800; // 2024-01-15 12:30:00 UTC
        $instant = CarbonInstant::fromTimestamp($timestamp);

        $this->assertSame($timestamp, $instant->toTimestamp());
    }

    public function testFromTimestampWithTimezone(): void
    {
        $timestamp = 1705321800;
        $instant = CarbonInstant::fromTimestamp($timestamp, Timezone::EUROPE_WARSAW);

        $this->assertSame('Europe/Warsaw', $instant->getTimezone()->getName());
    }

    public function testNowReturnsCurrentTime(): void
    {
        $instant = CarbonInstant::now();

        $this->assertSame('2024-06-15 12:00:00', $instant->format(TimeFormat::DATETIME));
    }

    public function testCreateWithAllComponents(): void
    {
        $instant = CarbonInstant::create(2024, 7, 4, 14, 30, 45);

        $this->assertSame('2024-07-04', $instant->format(TimeFormat::DATE));
        $this->assertSame('14:30:45', $instant->format(TimeFormat::TIME));
    }

    public function testCreateWithTimezone(): void
    {
        $instant = CarbonInstant::create(2024, 5, 1, timezone: Timezone::AMERICA_NEW_YORK);

        $this->assertSame('America/New_York', $instant->getTimezone()->getName());
    }

    // ========================================
    // Carbon-Specific Tests
    // ========================================

    public function testGetCarbonReturnsCarbon(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 10:00:00');

        $this->assertInstanceOf(CarbonImmutable::class, $instant->getCarbon());
    }

    public function testDiffForHumansFromNow(): void
    {
        $yesterday = CarbonInstant::parse('2024-06-14 12:00:00');

        $this->assertSame('1 day ago', $yesterday->diffForHumans());
    }

    public function testDiffForHumansFromOther(): void
    {
        $date1 = CarbonInstant::parse('2024-06-15 12:00:00');
        $date2 = CarbonInstant::parse('2024-06-18 12:00:00');

        $this->assertSame('3 days before', $date1->diffForHumans($date2->toDatetime()));
    }

    public function testLocale(): void
    {
        $instant = CarbonInstant::parse('2024-06-14 12:00:00');
        $localized = $instant->locale('pl');

        $this->assertInstanceOf(CarbonInstant::class, $localized);
        $this->assertSame('1 dzień temu', $localized->diffForHumans());
    }

    public function testIsoFormat(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 14:30:00');

        $this->assertSame('January 15, 2024', $instant->isoFormat('MMMM D, YYYY'));
    }

    public function testDayName(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 14:30:00'); // Monday

        $this->assertSame('Monday', $instant->dayName());
    }

    public function testMonthName(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 14:30:00');

        $this->assertSame('January', $instant->monthName());
    }

    // ========================================
    // Comparison Tests
    // ========================================

    public function testIsBefore(): void
    {
        $earlier = CarbonInstant::parse('2024-01-01 10:00:00');
        $later = CarbonInstant::parse('2024-01-01 12:00:00');

        $this->assertTrue($earlier->isBefore($later));
        $this->assertFalse($later->isBefore($earlier));
    }

    public function testIsAfter(): void
    {
        $earlier = CarbonInstant::parse('2024-01-01 10:00:00');
        $later = CarbonInstant::parse('2024-01-01 12:00:00');

        $this->assertTrue($later->isAfter($earlier));
        $this->assertFalse($earlier->isAfter($later));
    }

    public function testIsBeforeOrEqual(): void
    {
        $instant1 = CarbonInstant::parse('2024-01-01 10:00:00');
        $instant2 = CarbonInstant::parse('2024-01-01 10:00:00');

        $this->assertTrue($instant1->isBeforeOrEqual($instant2));
    }

    public function testIsAfterOrEqual(): void
    {
        $instant1 = CarbonInstant::parse('2024-01-01 14:00:00');
        $instant2 = CarbonInstant::parse('2024-01-01 14:00:00');

        $this->assertTrue($instant1->isAfterOrEqual($instant2));
    }

    public function testEquals(): void
    {
        $instant1 = CarbonInstant::parse('2024-01-01 10:00:00');
        $instant2 = CarbonInstant::parse('2024-01-01 10:00:00');

        $this->assertTrue($instant1->equals($instant2));
    }

    public function testIsBetween(): void
    {
        $start = CarbonInstant::parse('2024-01-01 10:00:00');
        $middle = CarbonInstant::parse('2024-01-01 12:00:00');
        $end = CarbonInstant::parse('2024-01-01 14:00:00');

        $this->assertTrue($middle->isBetween($start, $end));
    }

    public function testIsPast(): void
    {
        $past = CarbonInstant::parse('2024-01-01 10:00:00');

        $this->assertTrue($past->isPast());
    }

    public function testIsFuture(): void
    {
        $future = CarbonInstant::parse('2024-12-31 10:00:00');

        $this->assertTrue($future->isFuture());
    }

    public function testIsToday(): void
    {
        $today = CarbonInstant::parse('2024-06-15 08:30:00');

        $this->assertTrue($today->isToday());
    }

    public function testIsTomorrow(): void
    {
        $tomorrow = CarbonInstant::parse('2024-06-16 08:30:00');

        $this->assertTrue($tomorrow->isTomorrow());
    }

    public function testIsYesterday(): void
    {
        $yesterday = CarbonInstant::parse('2024-06-14 08:30:00');

        $this->assertTrue($yesterday->isYesterday());
    }

    public function testComparisonWithInstant(): void
    {
        $carbonInstant = CarbonInstant::parse('2024-01-01 10:00:00');
        $instant = Instant::parse('2024-01-01 12:00:00');

        $this->assertTrue($carbonInstant->isBefore($instant));
    }

    // ========================================
    // Arithmetic Tests
    // ========================================

    public function testAddSecondsReturnsCarbonInstant(): void
    {
        $original = CarbonInstant::parse('2024-01-01 10:00:00');
        $result = $original->addSeconds(30);

        $this->assertInstanceOf(CarbonInstant::class, $result);
        $this->assertSame('2024-01-01 10:00:30', $result->format(TimeFormat::DATETIME));
    }

    public function testAddMinutes(): void
    {
        $instant = CarbonInstant::parse('2024-01-01 10:00:00');

        $this->assertSame('2024-01-01 10:45:00', $instant->addMinutes(45)->format(TimeFormat::DATETIME));
    }

    public function testAddHours(): void
    {
        $instant = CarbonInstant::parse('2024-01-01 10:00:00');

        $this->assertSame('2024-01-01 16:00:00', $instant->addHours(6)->format(TimeFormat::DATETIME));
    }

    public function testAddDays(): void
    {
        $instant = CarbonInstant::parse('2024-01-01 10:00:00');

        $this->assertSame('2024-01-08 10:00:00', $instant->addDays(7)->format(TimeFormat::DATETIME));
    }

    public function testAddWeeks(): void
    {
        $instant = CarbonInstant::parse('2024-01-01 10:00:00');

        $this->assertSame('2024-01-15 10:00:00', $instant->addWeeks(2)->format(TimeFormat::DATETIME));
    }

    public function testAddMonths(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 10:00:00');

        $this->assertSame('2024-04-15 10:00:00', $instant->addMonths(3)->format(TimeFormat::DATETIME));
    }

    public function testAddYears(): void
    {
        $instant = CarbonInstant::parse('2024-01-01 10:00:00');

        $this->assertSame('2026-01-01 10:00:00', $instant->addYears(2)->format(TimeFormat::DATETIME));
    }

    public function testSubSeconds(): void
    {
        $instant = CarbonInstant::parse('2024-01-01 10:00:30');

        $this->assertSame('2024-01-01 10:00:00', $instant->subSeconds(30)->format(TimeFormat::DATETIME));
    }

    public function testSubMinutes(): void
    {
        $instant = CarbonInstant::parse('2024-01-01 10:45:00');

        $this->assertSame('2024-01-01 10:00:00', $instant->subMinutes(45)->format(TimeFormat::DATETIME));
    }

    public function testSubHours(): void
    {
        $instant = CarbonInstant::parse('2024-01-01 16:00:00');

        $this->assertSame('2024-01-01 10:00:00', $instant->subHours(6)->format(TimeFormat::DATETIME));
    }

    public function testSubDays(): void
    {
        $instant = CarbonInstant::parse('2024-01-08 10:00:00');

        $this->assertSame('2024-01-01 10:00:00', $instant->subDays(7)->format(TimeFormat::DATETIME));
    }

    public function testSubWeeks(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 10:00:00');

        $this->assertSame('2024-01-01 10:00:00', $instant->subWeeks(2)->format(TimeFormat::DATETIME));
    }

    public function testSubMonths(): void
    {
        $instant = CarbonInstant::parse('2024-04-15 10:00:00');

        $this->assertSame('2024-01-15 10:00:00', $instant->subMonths(3)->format(TimeFormat::DATETIME));
    }

    public function testSubYears(): void
    {
        $instant = CarbonInstant::parse('2026-01-01 10:00:00');

        $this->assertSame('2024-01-01 10:00:00', $instant->subYears(2)->format(TimeFormat::DATETIME));
    }

    public function testAddDateInterval(): void
    {
        $instant = CarbonInstant::parse('2024-01-01 10:00:00');
        $interval = new DateInterval('P1DT2H30M');

        $result = $instant->add($interval);

        $this->assertInstanceOf(CarbonInstant::class, $result);
        $this->assertSame('2024-01-02 12:30:00', $result->format(TimeFormat::DATETIME));
    }

    public function testSubDateInterval(): void
    {
        $instant = CarbonInstant::parse('2024-01-02 12:30:00');
        $interval = new DateInterval('P1DT2H30M');

        $result = $instant->sub($interval);

        $this->assertInstanceOf(CarbonInstant::class, $result);
        $this->assertSame('2024-01-01 10:00:00', $result->format(TimeFormat::DATETIME));
    }

    public function testModify(): void
    {
        $instant = CarbonInstant::parse('2024-01-01 10:00:00');

        $result = $instant->modify('+1 day');

        $this->assertInstanceOf(CarbonInstant::class, $result);
        $this->assertSame('2024-01-02 10:00:00', $result->format(TimeFormat::DATETIME));
    }

    public function testImmutability(): void
    {
        $original = CarbonInstant::parse('2024-01-01 10:00:00');
        $modified = $original->addDays(1);

        $this->assertNotSame($original, $modified);
        $this->assertSame('2024-01-01 10:00:00', $original->format(TimeFormat::DATETIME));
        $this->assertSame('2024-01-02 10:00:00', $modified->format(TimeFormat::DATETIME));
    }

    // ========================================
    // Difference Tests
    // ========================================

    public function testDiff(): void
    {
        $instant1 = CarbonInstant::parse('2024-01-01 10:00:00');
        $instant2 = CarbonInstant::parse('2024-01-03 12:30:45');

        $diff = $instant1->diff($instant2);

        $this->assertInstanceOf(DateInterval::class, $diff);
    }

    public function testDiffInSeconds(): void
    {
        $instant1 = CarbonInstant::parse('2024-01-01 10:01:30');
        $instant2 = CarbonInstant::parse('2024-01-01 10:00:00');

        // Carbon returns negative when comparing to an earlier date
        $this->assertSame(-90, $instant1->diffInSeconds($instant2));
    }

    public function testDiffInMinutes(): void
    {
        $instant1 = CarbonInstant::parse('2024-01-01 12:30:00');
        $instant2 = CarbonInstant::parse('2024-01-01 10:00:00');

        $this->assertSame(-150, $instant1->diffInMinutes($instant2));
    }

    public function testDiffInHours(): void
    {
        $instant1 = CarbonInstant::parse('2024-01-01 16:00:00');
        $instant2 = CarbonInstant::parse('2024-01-01 10:00:00');

        $this->assertSame(-6, $instant1->diffInHours($instant2));
    }

    public function testDiffInDays(): void
    {
        $instant1 = CarbonInstant::parse('2024-01-08 10:00:00');
        $instant2 = CarbonInstant::parse('2024-01-01 10:00:00');

        $this->assertSame(-7, $instant1->diffInDays($instant2));
    }

    // ========================================
    // Boundary Tests
    // ========================================

    public function testStartOfDay(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 14:30:45');

        $result = $instant->startOfDay();

        $this->assertInstanceOf(CarbonInstant::class, $result);
        $this->assertSame('2024-01-15 00:00:00', $result->format(TimeFormat::DATETIME));
    }

    public function testEndOfDay(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 14:30:45');
        $endOfDay = $instant->endOfDay();

        $this->assertInstanceOf(CarbonInstant::class, $endOfDay);
        $this->assertSame('2024-01-15', $endOfDay->format(TimeFormat::DATE));
        $this->assertSame('23:59:59', $endOfDay->format(TimeFormat::TIME));
    }

    public function testStartOfWeek(): void
    {
        // Wednesday
        $instant = CarbonInstant::parse('2024-01-17 14:30:45');

        $result = $instant->startOfWeek();

        $this->assertInstanceOf(CarbonInstant::class, $result);
        $this->assertSame('2024-01-15 00:00:00', $result->format(TimeFormat::DATETIME)); // Monday
    }

    public function testEndOfWeek(): void
    {
        // Wednesday
        $instant = CarbonInstant::parse('2024-01-17 14:30:45');
        $endOfWeek = $instant->endOfWeek();

        $this->assertInstanceOf(CarbonInstant::class, $endOfWeek);
        $this->assertSame('2024-01-21', $endOfWeek->format(TimeFormat::DATE)); // Sunday
    }

    public function testStartOfMonth(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 14:30:45');

        $result = $instant->startOfMonth();

        $this->assertInstanceOf(CarbonInstant::class, $result);
        $this->assertSame('2024-01-01 00:00:00', $result->format(TimeFormat::DATETIME));
    }

    public function testEndOfMonth(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 14:30:45');
        $endOfMonth = $instant->endOfMonth();

        $this->assertInstanceOf(CarbonInstant::class, $endOfMonth);
        $this->assertSame('2024-01-31', $endOfMonth->format(TimeFormat::DATE));
    }

    public function testStartOfYear(): void
    {
        $instant = CarbonInstant::parse('2024-06-15 14:30:45');

        $result = $instant->startOfYear();

        $this->assertInstanceOf(CarbonInstant::class, $result);
        $this->assertSame('2024-01-01 00:00:00', $result->format(TimeFormat::DATETIME));
    }

    public function testEndOfYear(): void
    {
        $instant = CarbonInstant::parse('2024-06-15 14:30:45');
        $endOfYear = $instant->endOfYear();

        $this->assertInstanceOf(CarbonInstant::class, $endOfYear);
        $this->assertSame('2024-12-31', $endOfYear->format(TimeFormat::DATE));
    }

    // ========================================
    // Formatting Tests
    // ========================================

    public function testFormatWithTimeFormatEnum(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 14:30:45');

        $this->assertSame('2024-01-15', $instant->format(TimeFormat::DATE));
        $this->assertSame('14:30:45', $instant->format(TimeFormat::TIME));
        $this->assertSame('2024-01-15 14:30:45', $instant->format(TimeFormat::DATETIME));
    }

    public function testFormatWithCustomString(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 14:30:45');

        $this->assertSame('15/01/2024', $instant->format('d/m/Y'));
    }

    public function testToDatetime(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 14:30:45');
        $datetime = $instant->toDatetime();

        $this->assertInstanceOf(\DateTimeImmutable::class, $datetime);
        $this->assertSame('2024-01-15 14:30:45', $datetime->format('Y-m-d H:i:s'));
    }

    public function testToTimestamp(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 12:30:00 UTC');

        $this->assertIsInt($instant->toTimestamp());
    }

    public function testToInstant(): void
    {
        $carbonInstant = CarbonInstant::parse('2024-01-15 14:30:45');
        $instant = $carbonInstant->toInstant();

        $this->assertInstanceOf(Instant::class, $instant);
        $this->assertSame('2024-01-15 14:30:45', $instant->format(TimeFormat::DATETIME));
    }

    public function testToDatetimeStringStatic(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 14:30:45');

        $this->assertSame('2024-01-15 14:30:45', CarbonInstant::toDatetimeString($instant));
    }

    public function testToString(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 14:30:45 UTC');

        $this->assertStringContainsString('2024-01-15T14:30:45', (string) $instant);
    }

    // ========================================
    // DateTime-like Accessor Tests
    // ========================================

    public function testGetTimezone(): void
    {
        $instant = CarbonInstant::create(2024, 1, 15, timezone: Timezone::EUROPE_WARSAW);

        $this->assertSame('Europe/Warsaw', $instant->getTimezone()->getName());
    }

    public function testGetOffset(): void
    {
        $instant = CarbonInstant::create(2024, 1, 15, timezone: Timezone::UTC);

        $this->assertSame(0, $instant->getOffset());
    }

    public function testGetTimestamp(): void
    {
        $instant = CarbonInstant::parse('2024-01-15 12:30:00 UTC');

        $this->assertIsInt($instant->getTimestamp());
    }

    public function testGetMicrosecond(): void
    {
        $instant = CarbonInstant::create(2024, 1, 15, 10, 0, 0, 123456);

        $this->assertSame(123456, $instant->getMicrosecond());
    }

    // ========================================
    // CarbonClock Integration Tests
    // ========================================

    public function testCarbonClockInstantMethod(): void
    {
        $clock = new CarbonClock();
        $instant = $clock->instant();

        $this->assertInstanceOf(Instant::class, $instant);
    }

    public function testCarbonClockCarbonInstantMethod(): void
    {
        $clock = new CarbonClock();
        $instant = $clock->carbonInstant();

        $this->assertInstanceOf(CarbonInstant::class, $instant);
    }

    // ========================================
    // ORM Integration Tests
    // ========================================

    public function testCastableFromAndToDatetimeString(): void
    {
        $dbValue = '2024-01-15 10:30:00';

        $instant = CarbonInstant::from($dbValue);
        $this->assertInstanceOf(CarbonInstant::class, $instant);

        $castBack = CarbonInstant::toDatetimeString($instant);
        $this->assertSame($dbValue, $castBack);
    }
}
