<?php

declare(strict_types=1);

namespace Larafony\Clock\Carbon\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Larafony\Clock\Carbon\CarbonClock;
use Larafony\Framework\Clock\Contracts\Clock;
use Larafony\Framework\Clock\Enums\TimeFormat;
use Larafony\Framework\Clock\Enums\Timezone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

#[CoversClass(CarbonClock::class)]
final class CarbonClockTest extends TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow(null);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
    }

    public function testImplementsClockInterface(): void
    {
        $clock = new CarbonClock();

        $this->assertInstanceOf(Clock::class, $clock);
        $this->assertInstanceOf(ClockInterface::class, $clock);
    }

    public function testReturnsCurrentTime(): void
    {
        $clock = new CarbonClock();
        $now = $clock->now();

        $this->assertInstanceOf(\DateTimeImmutable::class, $now);
        $this->assertEqualsWithDelta(time(), $now->getTimestamp(), 1);
    }

    public function testFormatsTimeWithEnum(): void
    {
        CarbonClock::withTestNow('2024-06-15 14:30:00');

        $clock = new CarbonClock();

        $this->assertSame('2024-06-15', $clock->format(TimeFormat::DATE));
        $this->assertSame('14:30:00', $clock->format(TimeFormat::TIME));
    }

    public function testFormatsTimeWithString(): void
    {
        CarbonClock::withTestNow('2024-06-15 14:30:00');

        $clock = new CarbonClock();

        $this->assertSame('15/06/2024', $clock->format('d/m/Y'));
    }

    public function testReturnsTimestamp(): void
    {
        CarbonClock::withTestNow('2024-06-15 14:30:00');

        $clock = new CarbonClock();

        $this->assertSame(strtotime('2024-06-15 14:30:00'), $clock->timestamp());
    }

    public function testChecksIfDateIsPast(): void
    {
        CarbonClock::withTestNow('2024-06-15 14:30:00');

        $clock = new CarbonClock();

        $this->assertTrue($clock->isPast(new \DateTimeImmutable('2024-06-14')));
        $this->assertFalse($clock->isPast(new \DateTimeImmutable('2024-06-16')));
    }

    public function testChecksIfDateIsFuture(): void
    {
        CarbonClock::withTestNow('2024-06-15 14:30:00');

        $clock = new CarbonClock();

        $this->assertTrue($clock->isFuture(new \DateTimeImmutable('2024-06-16')));
        $this->assertFalse($clock->isFuture(new \DateTimeImmutable('2024-06-14')));
    }

    public function testChecksIfDateIsToday(): void
    {
        CarbonClock::withTestNow('2024-06-15 14:30:00');

        $clock = new CarbonClock();

        $this->assertTrue($clock->isToday(new \DateTimeImmutable('2024-06-15 08:00:00')));
        $this->assertFalse($clock->isToday(new \DateTimeImmutable('2024-06-14')));
    }

    public function testParsesDateString(): void
    {
        $clock = new CarbonClock();
        $parsed = $clock->parse('2024-06-15 14:30:00');

        $this->assertInstanceOf(CarbonClock::class, $parsed);
        $this->assertSame('2024-06-15 14:30:00', $parsed->format('Y-m-d H:i:s'));
    }

    public function testCreatesFromTimezone(): void
    {
        $clock = CarbonClock::fromTimezone(Timezone::EUROPE_WARSAW);

        $this->assertInstanceOf(CarbonClock::class, $clock);
    }

    public function testCreatesFromString(): void
    {
        $clock = CarbonClock::from('2024-06-15 14:30:00');

        $this->assertSame('2024-06-15 14:30:00', $clock->format('Y-m-d H:i:s'));
    }

    public function testExposesCarbonInstance(): void
    {
        $clock = new CarbonClock();

        $this->assertInstanceOf(CarbonImmutable::class, $clock->getCarbon());
    }

    public function testExposesMutableCarbonInstance(): void
    {
        $clock = new CarbonClock();

        $this->assertInstanceOf(Carbon::class, $clock->getMutableCarbon());
    }

    public function testSupportsTestNow(): void
    {
        CarbonClock::withTestNow('2024-01-01 00:00:00');

        $clock = new CarbonClock();

        $this->assertTrue(CarbonClock::hasTestNow());
        $this->assertSame('2024-01-01 00:00:00', $clock->format('Y-m-d H:i:s'));

        CarbonClock::withTestNow(null);
        $this->assertFalse(CarbonClock::hasTestNow());
    }

    public function testProvidesDiffForHumans(): void
    {
        CarbonClock::withTestNow('2024-06-15 14:30:00');

        $clock = new CarbonClock();
        $parsed = $clock->parse('2024-06-14 14:30:00');

        $this->assertSame('1 day ago', $parsed->diffForHumans());
    }

    public function testAddsTimeInterval(): void
    {
        CarbonClock::withTestNow('2024-06-15 14:30:00');

        $clock = new CarbonClock();
        $future = $clock->add('1 day');

        $this->assertSame('2024-06-16', $future->format('Y-m-d'));
    }

    public function testSubtractsTimeInterval(): void
    {
        CarbonClock::withTestNow('2024-06-15 14:30:00');

        $clock = new CarbonClock();
        $past = $clock->sub('1 week');

        $this->assertSame('2024-06-08', $past->format('Y-m-d'));
    }

    public function testGetsStartOfUnit(): void
    {
        CarbonClock::withTestNow('2024-06-15 14:30:00');

        $clock = new CarbonClock();

        $this->assertSame('2024-06-15 00:00:00', $clock->startOf('day')->format('Y-m-d H:i:s'));
        $this->assertSame('2024-06-01 00:00:00', $clock->startOf('month')->format('Y-m-d H:i:s'));
    }

    public function testGetsEndOfUnit(): void
    {
        CarbonClock::withTestNow('2024-06-15 14:30:00');

        $clock = new CarbonClock();

        $this->assertSame('2024-06-15 23:59:59', $clock->endOf('day')->format('Y-m-d H:i:s'));
    }

    public function testCreatesSpecificDate(): void
    {
        $clock = CarbonClock::create(2024, 6, 15, 14, 30, 0);

        $this->assertSame('2024-06-15 14:30:00', $clock->format('Y-m-d H:i:s'));
    }

    public function testReturnsMilliseconds(): void
    {
        $clock = new CarbonClock();
        $ms = $clock->milliseconds();

        $this->assertIsInt($ms);
        $this->assertGreaterThan($clock->timestamp() * 1000, $ms + 1000);
    }

    public function testReturnsMicroseconds(): void
    {
        $clock = new CarbonClock();
        $us = $clock->microseconds();

        $this->assertIsInt($us);
        $this->assertGreaterThan($clock->timestamp() * 1000000, $us + 1000000);
    }
}
