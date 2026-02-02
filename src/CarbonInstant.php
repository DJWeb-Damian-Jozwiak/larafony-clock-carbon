<?php

declare(strict_types=1);

namespace Larafony\Clock\Carbon;

use Carbon\CarbonImmutable;
use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use Larafony\Framework\Clock\ClockFactory;
use Larafony\Framework\Clock\Enums\TimeFormat;
use Larafony\Framework\Clock\Enums\Timezone;
use Larafony\Framework\Clock\Instant;
use Larafony\Framework\Database\ORM\Contracts\Castable;
use Stringable;

/**
 * Carbon-powered immutable instant value object.
 *
 * Extends the base Instant functionality with Carbon's rich features
 * like diffForHumans(), locale support, and isoFormat().
 *
 * Use CarbonInstant when you need human-readable output or localization.
 * For simple date/time operations, the base Instant class may be sufficient.
 */
final readonly class CarbonInstant implements Castable, Stringable
{
    private CarbonImmutable $carbon;

    private function __construct(CarbonImmutable $carbon)
    {
        $this->carbon = $carbon;
    }

    // ========================================
    // Factory Methods
    // ========================================

    /**
     * Create a CarbonInstant from a string value (Castable interface).
     */
    public static function from(string $value): static
    {
        return new self(CarbonImmutable::parse($value));
    }

    /**
     * Parse a datetime string into a CarbonInstant.
     */
    public static function parse(string $value): self
    {
        return new self(CarbonImmutable::parse($value));
    }

    /**
     * Create a CarbonInstant from a DateTimeInterface.
     */
    public static function fromDateTime(DateTimeInterface $datetime): self
    {
        return new self(CarbonImmutable::instance($datetime));
    }

    /**
     * Create a CarbonInstant from an Instant.
     */
    public static function fromInstant(Instant $instant): self
    {
        return new self(CarbonImmutable::instance($instant->toDatetime()));
    }

    /**
     * Create a CarbonInstant from a Unix timestamp.
     */
    public static function fromTimestamp(int $timestamp, ?Timezone $timezone = null): self
    {
        $tz = $timezone !== null ? $timezone->value : 'UTC';

        return new self(CarbonImmutable::createFromTimestamp($timestamp, $tz));
    }

    /**
     * Create a CarbonInstant representing the current time.
     */
    public static function now(): self
    {
        return self::fromDateTime(ClockFactory::now());
    }

    /**
     * Create a CarbonInstant from individual date/time components.
     */
    public static function create(
        int $year,
        int $month = 1,
        int $day = 1,
        int $hour = 0,
        int $minute = 0,
        int $second = 0,
        int $microsecond = 0,
        ?Timezone $timezone = null,
    ): self {
        $tz = $timezone !== null ? $timezone->value : 'UTC';
        $carbon = CarbonImmutable::create($year, $month, $day, $hour, $minute, $second, $tz);

        if ($carbon === null) {
            throw new \InvalidArgumentException('Failed to create CarbonImmutable from given parameters');
        }

        return new self($carbon->microsecond($microsecond));
    }

    // ========================================
    // Carbon-Specific Methods
    // ========================================

    /**
     * Get the underlying CarbonImmutable instance.
     */
    public function getCarbon(): CarbonImmutable
    {
        return $this->carbon;
    }

    /**
     * Get human-readable difference from now or another date.
     *
     * Example: "2 hours ago", "in 3 days", "1 week before"
     */
    public function diffForHumans(?DateTimeInterface $other = null): string
    {
        if ($other !== null) {
            return $this->carbon->diffForHumans($other);
        }

        return $this->carbon->diffForHumans();
    }

    /**
     * Set the locale for human-readable output.
     *
     * Returns a new instance with the locale set.
     */
    public function locale(string $locale): self
    {
        /** @var CarbonImmutable $localized */
        $localized = $this->carbon->locale($locale);

        return new self($localized);
    }

    /**
     * Format using ICU format patterns.
     *
     * Example patterns:
     * - 'LLLL d, yyyy' => "January 15, 2024"
     * - 'EEE, MMM d' => "Mon, Jan 15"
     */
    public function isoFormat(string $format): string
    {
        return $this->carbon->isoFormat($format);
    }

    /**
     * Get the translated day name.
     */
    public function dayName(): string
    {
        return $this->carbon->dayName;
    }

    /**
     * Get the translated month name.
     */
    public function monthName(): string
    {
        return $this->carbon->monthName;
    }

    // ========================================
    // Comparison Methods
    // ========================================

    /**
     * Check if this instant is before another.
     */
    public function isBefore(DateTimeInterface|Instant|self $other): bool
    {
        return $this->carbon->isBefore($this->toCarbonInstance($other));
    }

    /**
     * Check if this instant is after another.
     */
    public function isAfter(DateTimeInterface|Instant|self $other): bool
    {
        return $this->carbon->isAfter($this->toCarbonInstance($other));
    }

    /**
     * Check if this instant is before or equal to another.
     */
    public function isBeforeOrEqual(DateTimeInterface|Instant|self $other): bool
    {
        return $this->carbon->lessThanOrEqualTo($this->toCarbonInstance($other));
    }

    /**
     * Check if this instant is after or equal to another.
     */
    public function isAfterOrEqual(DateTimeInterface|Instant|self $other): bool
    {
        return $this->carbon->greaterThanOrEqualTo($this->toCarbonInstance($other));
    }

    /**
     * Check if this instant equals another.
     */
    public function equals(DateTimeInterface|Instant|self $other): bool
    {
        return $this->carbon->equalTo($this->toCarbonInstance($other));
    }

    /**
     * Check if this instant is between two others.
     */
    public function isBetween(
        DateTimeInterface|Instant|self $start,
        DateTimeInterface|Instant|self $end,
        bool $inclusive = true,
    ): bool {
        return $this->carbon->between(
            $this->toCarbonInstance($start),
            $this->toCarbonInstance($end),
            $inclusive,
        );
    }

    /**
     * Check if this instant is in the past.
     */
    public function isPast(): bool
    {
        return $this->carbon->isPast();
    }

    /**
     * Check if this instant is in the future.
     */
    public function isFuture(): bool
    {
        return $this->carbon->isFuture();
    }

    /**
     * Check if this instant is today.
     */
    public function isToday(): bool
    {
        return $this->carbon->isToday();
    }

    /**
     * Check if this instant is tomorrow.
     */
    public function isTomorrow(): bool
    {
        return $this->carbon->isTomorrow();
    }

    /**
     * Check if this instant is yesterday.
     */
    public function isYesterday(): bool
    {
        return $this->carbon->isYesterday();
    }

    // ========================================
    // Arithmetic Methods (return new CarbonInstant)
    // ========================================

    /**
     * Add seconds to this instant.
     */
    public function addSeconds(int $seconds): self
    {
        return new self($this->carbon->addSeconds($seconds));
    }

    /**
     * Add minutes to this instant.
     */
    public function addMinutes(int $minutes): self
    {
        return new self($this->carbon->addMinutes($minutes));
    }

    /**
     * Add hours to this instant.
     */
    public function addHours(int $hours): self
    {
        return new self($this->carbon->addHours($hours));
    }

    /**
     * Add days to this instant.
     */
    public function addDays(int $days): self
    {
        return new self($this->carbon->addDays($days));
    }

    /**
     * Add weeks to this instant.
     */
    public function addWeeks(int $weeks): self
    {
        return new self($this->carbon->addWeeks($weeks));
    }

    /**
     * Add months to this instant.
     */
    public function addMonths(int $months): self
    {
        return new self($this->carbon->addMonths($months));
    }

    /**
     * Add years to this instant.
     */
    public function addYears(int $years): self
    {
        return new self($this->carbon->addYears($years));
    }

    /**
     * Subtract seconds from this instant.
     */
    public function subSeconds(int $seconds): self
    {
        return new self($this->carbon->subSeconds($seconds));
    }

    /**
     * Subtract minutes from this instant.
     */
    public function subMinutes(int $minutes): self
    {
        return new self($this->carbon->subMinutes($minutes));
    }

    /**
     * Subtract hours from this instant.
     */
    public function subHours(int $hours): self
    {
        return new self($this->carbon->subHours($hours));
    }

    /**
     * Subtract days from this instant.
     */
    public function subDays(int $days): self
    {
        return new self($this->carbon->subDays($days));
    }

    /**
     * Subtract weeks from this instant.
     */
    public function subWeeks(int $weeks): self
    {
        return new self($this->carbon->subWeeks($weeks));
    }

    /**
     * Subtract months from this instant.
     */
    public function subMonths(int $months): self
    {
        return new self($this->carbon->subMonths($months));
    }

    /**
     * Subtract years from this instant.
     */
    public function subYears(int $years): self
    {
        return new self($this->carbon->subYears($years));
    }

    /**
     * Add a DateInterval to this instant.
     */
    public function add(DateInterval $interval): self
    {
        return new self($this->carbon->add($interval));
    }

    /**
     * Subtract a DateInterval from this instant.
     */
    public function sub(DateInterval $interval): self
    {
        return new self($this->carbon->sub($interval));
    }

    /**
     * Modify this instant using a relative string format.
     */
    public function modify(string $modifier): self
    {
        return new self($this->carbon->modify($modifier));
    }

    // ========================================
    // Difference Methods
    // ========================================

    /**
     * Get the difference between this instant and another as a DateInterval.
     */
    public function diff(DateTimeInterface|Instant|self $other, bool $absolute = false): DateInterval
    {
        return $this->carbon->diff($this->toCarbonInstance($other), $absolute);
    }

    /**
     * Get the difference in seconds.
     */
    public function diffInSeconds(DateTimeInterface|Instant|self $other): int
    {
        return (int) $this->carbon->diffInSeconds($this->toCarbonInstance($other), absolute: false);
    }

    /**
     * Get the difference in minutes.
     */
    public function diffInMinutes(DateTimeInterface|Instant|self $other): int
    {
        return (int) $this->carbon->diffInMinutes($this->toCarbonInstance($other), absolute: false);
    }

    /**
     * Get the difference in hours.
     */
    public function diffInHours(DateTimeInterface|Instant|self $other): int
    {
        return (int) $this->carbon->diffInHours($this->toCarbonInstance($other), absolute: false);
    }

    /**
     * Get the difference in days.
     */
    public function diffInDays(DateTimeInterface|Instant|self $other): int
    {
        return (int) $this->carbon->diffInDays($this->toCarbonInstance($other), absolute: false);
    }

    // ========================================
    // Boundary Methods
    // ========================================

    /**
     * Get the start of day (00:00:00).
     */
    public function startOfDay(): self
    {
        return new self($this->carbon->startOfDay());
    }

    /**
     * Get the end of day (23:59:59.999999).
     */
    public function endOfDay(): self
    {
        return new self($this->carbon->endOfDay());
    }

    /**
     * Get the start of week (Monday 00:00:00).
     */
    public function startOfWeek(): self
    {
        return new self($this->carbon->startOfWeek());
    }

    /**
     * Get the end of week (Sunday 23:59:59.999999).
     */
    public function endOfWeek(): self
    {
        return new self($this->carbon->endOfWeek());
    }

    /**
     * Get the start of month (1st day 00:00:00).
     */
    public function startOfMonth(): self
    {
        return new self($this->carbon->startOfMonth());
    }

    /**
     * Get the end of month (last day 23:59:59.999999).
     */
    public function endOfMonth(): self
    {
        return new self($this->carbon->endOfMonth());
    }

    /**
     * Get the start of year (January 1st 00:00:00).
     */
    public function startOfYear(): self
    {
        return new self($this->carbon->startOfYear());
    }

    /**
     * Get the end of year (December 31st 23:59:59.999999).
     */
    public function endOfYear(): self
    {
        return new self($this->carbon->endOfYear());
    }

    // ========================================
    // Formatting Methods
    // ========================================

    /**
     * Format this instant using a TimeFormat enum or custom string.
     */
    public function format(TimeFormat|string $format): string
    {
        $formatString = $format instanceof TimeFormat ? $format->value : $format;

        return $this->carbon->format($formatString);
    }

    /**
     * Convert to DateTimeImmutable.
     */
    public function toDatetime(): \DateTimeImmutable
    {
        return $this->carbon->toDateTimeImmutable();
    }

    /**
     * Convert to Unix timestamp.
     */
    public function toTimestamp(): int
    {
        return $this->carbon->getTimestamp();
    }

    /**
     * Convert to base Instant.
     */
    public function toInstant(): Instant
    {
        return Instant::fromDateTime($this->carbon);
    }

    /**
     * Convert a CarbonInstant to a datetime string (static method for CastUsing castBack).
     */
    public static function toDatetimeString(self $instant): string
    {
        return $instant->format(TimeFormat::DATETIME);
    }

    /**
     * Convert to ISO8601 string.
     */
    public function __toString(): string
    {
        return $this->carbon->toAtomString();
    }

    // ========================================
    // DateTime-like Accessors
    // ========================================

    /**
     * Get the timezone of this instant.
     */
    public function getTimezone(): DateTimeZone
    {
        /** @var DateTimeZone $timezone */
        $timezone = $this->carbon->getTimezone();

        return $timezone;
    }

    /**
     * Get the UTC offset in seconds.
     */
    public function getOffset(): int
    {
        return $this->carbon->getOffset();
    }

    /**
     * Get the Unix timestamp.
     */
    public function getTimestamp(): int
    {
        return $this->carbon->getTimestamp();
    }

    /**
     * Get the microsecond component.
     */
    public function getMicrosecond(): int
    {
        return $this->carbon->micro;
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Convert input to CarbonImmutable.
     */
    private function toCarbonInstance(DateTimeInterface|Instant|self $value): CarbonImmutable
    {
        return match (true) {
            $value instanceof self => $value->carbon,
            $value instanceof Instant => CarbonImmutable::instance($value->toDatetime()),
            default => CarbonImmutable::instance($value),
        };
    }
}
