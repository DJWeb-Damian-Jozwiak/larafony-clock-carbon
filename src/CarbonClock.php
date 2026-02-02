<?php

declare(strict_types=1);

namespace Larafony\Clock\Carbon;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeZone;
use Larafony\Framework\Clock\Contracts\Clock;
use Larafony\Framework\Clock\Enums\TimeFormat;
use Larafony\Framework\Clock\Enums\Timezone;
use Larafony\Framework\Clock\Instant;
use Larafony\Framework\Database\ORM\Contracts\Castable;

/**
 * Carbon-based clock implementation for Larafony.
 *
 * Provides all Clock interface methods using Carbon library,
 * giving access to Carbon's rich date/time manipulation features.
 *
 * Features:
 * - Full Carbon API access via getCarbon()
 * - Compatible with Larafony's Clock interface
 * - Test time support via setTestNow()
 * - Timezone support
 * - Human-readable date formatting (diffForHumans)
 * - Date manipulation (add, sub, startOf, endOf)
 *
 * Example usage:
 * ```php
 * $clock = CarbonClock::fromTimezone(Timezone::EUROPE_WARSAW);
 * echo $clock->now()->format('Y-m-d H:i:s');
 *
 * // Access Carbon features
 * echo $clock->getCarbon()->diffForHumans();
 * echo $clock->getCarbon()->addDays(7)->toDateString();
 * ```
 */
final class CarbonClock implements Clock, Castable
{
    private ?CarbonImmutable $frozenTime = null;

    public function __construct(
        private readonly ?DateTimeZone $timezone = null,
    ) {
    }

    public static function fromTimezone(?Timezone $timezone = null): self
    {
        return new self(new DateTimeZone($timezone->value ?? 'UTC'));
    }

    /**
     * Create CarbonClock from datetime string (Castable interface).
     *
     * @param string $value DateTime string to parse
     *
     * @return static
     */
    public static function from(string $value): static
    {
        $clock = new self();
        $clock->frozenTime = CarbonImmutable::parse($value);
        return $clock;
    }

    /**
     * Get Carbon instance for current time.
     *
     * Provides direct access to Carbon's rich API.
     */
    public function getCarbon(): CarbonImmutable
    {
        if ($this->frozenTime !== null) {
            return $this->frozenTime;
        }

        if (Carbon::hasTestNow()) {
            return CarbonImmutable::instance(Carbon::getTestNow());
        }

        return CarbonImmutable::now($this->timezone);
    }

    /**
     * Get mutable Carbon instance.
     *
     * Use when you need to modify dates without creating new instances.
     */
    public function getMutableCarbon(): Carbon
    {
        return Carbon::instance($this->getCarbon());
    }

    public function format(TimeFormat|string $format): string
    {
        $format = is_string($format) ? $format : $format->value;
        return $this->getCarbon()->format($format);
    }

    public function now(): \DateTimeImmutable
    {
        return $this->getCarbon()->toDateTimeImmutable();
    }

    /**
     * Set a fixed time for testing (Carbon-compatible API).
     *
     * @param \DateTimeImmutable|\DateTimeInterface|string|null $testNow
     */
    public static function withTestNow(\DateTimeImmutable|\DateTimeInterface|string|null $testNow = null): void
    {
        if ($testNow === null) {
            Carbon::setTestNow(null);
            return;
        }

        Carbon::setTestNow(
            match (true) {
                $testNow instanceof \DateTimeInterface => $testNow,
                default => CarbonImmutable::parse($testNow),
            }
        );
    }

    /**
     * Check if test time is set.
     */
    public static function hasTestNow(): bool
    {
        return Carbon::hasTestNow();
    }

    public function timestamp(): int
    {
        return $this->getCarbon()->getTimestamp();
    }

    /**
     * Get current timestamp in milliseconds.
     */
    public function milliseconds(): int
    {
        return (int) $this->getCarbon()->getPreciseTimestamp(3);
    }

    /**
     * Get current timestamp in microseconds.
     */
    public function microseconds(): int
    {
        return (int) $this->getCarbon()->getPreciseTimestamp(6);
    }

    public function isPast(\DateTimeInterface $date): bool
    {
        return CarbonImmutable::instance($date)->isPast();
    }

    public function isFuture(\DateTimeInterface $date): bool
    {
        return CarbonImmutable::instance($date)->isFuture();
    }

    public function isToday(\DateTimeInterface $date): bool
    {
        return CarbonImmutable::instance($date)->isToday();
    }

    public function parse(string $date): self
    {
        $clock = new self($this->timezone);
        $clock->frozenTime = CarbonImmutable::parse($date, $this->timezone);
        return $clock;
    }

    /**
     * Get human-readable difference from now.
     *
     * @param \DateTimeInterface|null $other Compare to this date (null = now)
     */
    public function diffForHumans(?\DateTimeInterface $other = null): string
    {
        $carbon = $this->getCarbon();
        return $other !== null
            ? $carbon->diffForHumans($other)
            : $carbon->diffForHumans();
    }

    /**
     * Add interval to current time.
     *
     * @param string $interval Carbon interval string (e.g., '1 day', '2 weeks')
     */
    public function add(string $interval): self
    {
        $clock = new self($this->timezone);
        $clock->frozenTime = $this->getCarbon()->add($interval);
        return $clock;
    }

    /**
     * Subtract interval from current time.
     *
     * @param string $interval Carbon interval string (e.g., '1 day', '2 weeks')
     */
    public function sub(string $interval): self
    {
        $clock = new self($this->timezone);
        $clock->frozenTime = $this->getCarbon()->sub($interval);
        return $clock;
    }

    /**
     * Get start of a unit (day, week, month, year).
     */
    public function startOf(string $unit): self
    {
        $clock = new self($this->timezone);
        $clock->frozenTime = $this->getCarbon()->startOf($unit);
        return $clock;
    }

    /**
     * Get end of a unit (day, week, month, year).
     */
    public function endOf(string $unit): self
    {
        $clock = new self($this->timezone);
        $clock->frozenTime = $this->getCarbon()->endOf($unit);
        return $clock;
    }

    /**
     * Create a date from specific values.
     */
    public static function create(
        ?int $year = null,
        ?int $month = null,
        ?int $day = null,
        ?int $hour = null,
        ?int $minute = null,
        ?int $second = null,
        ?string $timezone = null,
    ): self {
        $clock = new self($timezone !== null ? new DateTimeZone($timezone) : null);
        $clock->frozenTime = CarbonImmutable::create($year, $month, $day, $hour, $minute, $second, $timezone);
        return $clock;
    }

    /**
     * Sleep for given seconds.
     */
    public function sleep(int $seconds): void
    {
        sleep($seconds);
    }

    /**
     * Sleep for given microseconds.
     */
    public function usleep(int $microseconds): void
    {
        usleep($microseconds);
    }

    /**
     * Get an Instant representing the current time.
     */
    public function instant(): Instant
    {
        return Instant::fromDateTime($this->now());
    }

    /**
     * Get a CarbonInstant representing the current time.
     */
    public function carbonInstant(): CarbonInstant
    {
        return CarbonInstant::fromDateTime($this->now());
    }
}
