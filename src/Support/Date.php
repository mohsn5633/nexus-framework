<?php

namespace Nexus\Support;

use DateTime;
use DateTimeZone;
use DateInterval;

/**
 * Date/Time Utility Class
 *
 * Carbon-like date manipulation class
 */
class Date extends DateTime
{
    /**
     * Create a new Date instance
     *
     * @param string|null $time
     * @param DateTimeZone|string|null $timezone
     */
    public function __construct(?string $time = 'now', DateTimeZone|string|null $timezone = null)
    {
        if (is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        }

        parent::__construct($time ?? 'now', $timezone ?? new DateTimeZone(date_default_timezone_get()));
    }

    /**
     * Create a new Date instance from current date and time
     *
     * @param DateTimeZone|string|null $timezone
     * @return static
     */
    public static function now(DateTimeZone|string|null $timezone = null): static
    {
        return new static('now', $timezone);
    }

    /**
     * Create a Date instance from a specific date and time
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param DateTimeZone|string|null $timezone
     * @return static
     */
    public static function create(
        int $year,
        int $month = 1,
        int $day = 1,
        int $hour = 0,
        int $minute = 0,
        int $second = 0,
        DateTimeZone|string|null $timezone = null
    ): static {
        return new static(
            sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second),
            $timezone
        );
    }

    /**
     * Parse a string into a Date instance
     *
     * @param string $time
     * @param DateTimeZone|string|null $timezone
     * @return static
     */
    public static function parse(string $time, DateTimeZone|string|null $timezone = null): static
    {
        return new static($time, $timezone);
    }

    /**
     * Create a Date instance for today
     *
     * @param DateTimeZone|string|null $timezone
     * @return static
     */
    public static function today(DateTimeZone|string|null $timezone = null): static
    {
        return static::now($timezone)->startOfDay();
    }

    /**
     * Create a Date instance for tomorrow
     *
     * @param DateTimeZone|string|null $timezone
     * @return static
     */
    public static function tomorrow(DateTimeZone|string|null $timezone = null): static
    {
        return static::today($timezone)->addDay();
    }

    /**
     * Create a Date instance for yesterday
     *
     * @param DateTimeZone|string|null $timezone
     * @return static
     */
    public static function yesterday(DateTimeZone|string|null $timezone = null): static
    {
        return static::today($timezone)->subDay();
    }

    /**
     * Add days
     *
     * @param int $days
     * @return static
     */
    public function addDays(int $days): static
    {
        return $this->add(new DateInterval("P{$days}D"));
    }

    /**
     * Add a single day
     *
     * @return static
     */
    public function addDay(): static
    {
        return $this->addDays(1);
    }

    /**
     * Subtract days
     *
     * @param int $days
     * @return static
     */
    public function subDays(int $days): static
    {
        return $this->sub(new DateInterval("P{$days}D"));
    }

    /**
     * Subtract a single day
     *
     * @return static
     */
    public function subDay(): static
    {
        return $this->subDays(1);
    }

    /**
     * Add weeks
     *
     * @param int $weeks
     * @return static
     */
    public function addWeeks(int $weeks): static
    {
        return $this->addDays($weeks * 7);
    }

    /**
     * Add a single week
     *
     * @return static
     */
    public function addWeek(): static
    {
        return $this->addWeeks(1);
    }

    /**
     * Subtract weeks
     *
     * @param int $weeks
     * @return static
     */
    public function subWeeks(int $weeks): static
    {
        return $this->subDays($weeks * 7);
    }

    /**
     * Subtract a single week
     *
     * @return static
     */
    public function subWeek(): static
    {
        return $this->subWeeks(1);
    }

    /**
     * Add months
     *
     * @param int $months
     * @return static
     */
    public function addMonths(int $months): static
    {
        return $this->add(new DateInterval("P{$months}M"));
    }

    /**
     * Add a single month
     *
     * @return static
     */
    public function addMonth(): static
    {
        return $this->addMonths(1);
    }

    /**
     * Subtract months
     *
     * @param int $months
     * @return static
     */
    public function subMonths(int $months): static
    {
        return $this->sub(new DateInterval("P{$months}M"));
    }

    /**
     * Subtract a single month
     *
     * @return static
     */
    public function subMonth(): static
    {
        return $this->subMonths(1);
    }

    /**
     * Add years
     *
     * @param int $years
     * @return static
     */
    public function addYears(int $years): static
    {
        return $this->add(new DateInterval("P{$years}Y"));
    }

    /**
     * Add a single year
     *
     * @return static
     */
    public function addYear(): static
    {
        return $this->addYears(1);
    }

    /**
     * Subtract years
     *
     * @param int $years
     * @return static
     */
    public function subYears(int $years): static
    {
        return $this->sub(new DateInterval("P{$years}Y"));
    }

    /**
     * Subtract a single year
     *
     * @return static
     */
    public function subYear(): static
    {
        return $this->subYears(1);
    }

    /**
     * Add hours
     *
     * @param int $hours
     * @return static
     */
    public function addHours(int $hours): static
    {
        return $this->add(new DateInterval("PT{$hours}H"));
    }

    /**
     * Add a single hour
     *
     * @return static
     */
    public function addHour(): static
    {
        return $this->addHours(1);
    }

    /**
     * Subtract hours
     *
     * @param int $hours
     * @return static
     */
    public function subHours(int $hours): static
    {
        return $this->sub(new DateInterval("PT{$hours}H"));
    }

    /**
     * Subtract a single hour
     *
     * @return static
     */
    public function subHour(): static
    {
        return $this->subHours(1);
    }

    /**
     * Add minutes
     *
     * @param int $minutes
     * @return static
     */
    public function addMinutes(int $minutes): static
    {
        return $this->add(new DateInterval("PT{$minutes}M"));
    }

    /**
     * Subtract minutes
     *
     * @param int $minutes
     * @return static
     */
    public function subMinutes(int $minutes): static
    {
        return $this->sub(new DateInterval("PT{$minutes}M"));
    }

    /**
     * Add seconds
     *
     * @param int $seconds
     * @return static
     */
    public function addSeconds(int $seconds): static
    {
        return $this->add(new DateInterval("PT{$seconds}S"));
    }

    /**
     * Subtract seconds
     *
     * @param int $seconds
     * @return static
     */
    public function subSeconds(int $seconds): static
    {
        return $this->sub(new DateInterval("PT{$seconds}S"));
    }

    /**
     * Set time to start of day (00:00:00)
     *
     * @return static
     */
    public function startOfDay(): static
    {
        return $this->setTime(0, 0, 0);
    }

    /**
     * Set time to end of day (23:59:59)
     *
     * @return static
     */
    public function endOfDay(): static
    {
        return $this->setTime(23, 59, 59);
    }

    /**
     * Set to start of month
     *
     * @return static
     */
    public function startOfMonth(): static
    {
        return $this->setDate((int)$this->format('Y'), (int)$this->format('m'), 1)->startOfDay();
    }

    /**
     * Set to end of month
     *
     * @return static
     */
    public function endOfMonth(): static
    {
        return $this->setDate((int)$this->format('Y'), (int)$this->format('m'), (int)$this->format('t'))->endOfDay();
    }

    /**
     * Format the date for humans (e.g., "2 hours ago")
     *
     * @return string
     */
    public function diffForHumans(): string
    {
        $now = new static();
        $diff = $now->getTimestamp() - $this->getTimestamp();

        if ($diff < 0) {
            $diff = abs($diff);
            $suffix = 'from now';
        } else {
            $suffix = 'ago';
        }

        if ($diff < 60) {
            return $diff . ' seconds ' . $suffix;
        }

        if ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' ' . ($minutes === 1 ? 'minute' : 'minutes') . ' ' . $suffix;
        }

        if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' ' . ($hours === 1 ? 'hour' : 'hours') . ' ' . $suffix;
        }

        if ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $days . ' ' . ($days === 1 ? 'day' : 'days') . ' ' . $suffix;
        }

        if ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . ' ' . ($months === 1 ? 'month' : 'months') . ' ' . $suffix;
        }

        $years = floor($diff / 31536000);
        return $years . ' ' . ($years === 1 ? 'year' : 'years') . ' ' . $suffix;
    }

    /**
     * Check if date is in the past
     *
     * @return bool
     */
    public function isPast(): bool
    {
        return $this->getTimestamp() < time();
    }

    /**
     * Check if date is in the future
     *
     * @return bool
     */
    public function isFuture(): bool
    {
        return $this->getTimestamp() > time();
    }

    /**
     * Check if date is today
     *
     * @return bool
     */
    public function isToday(): bool
    {
        return $this->format('Y-m-d') === date('Y-m-d');
    }

    /**
     * Check if date is tomorrow
     *
     * @return bool
     */
    public function isTomorrow(): bool
    {
        return $this->format('Y-m-d') === date('Y-m-d', strtotime('tomorrow'));
    }

    /**
     * Check if date is yesterday
     *
     * @return bool
     */
    public function isYesterday(): bool
    {
        return $this->format('Y-m-d') === date('Y-m-d', strtotime('yesterday'));
    }

    /**
     * Check if date is a weekend
     *
     * @return bool
     */
    public function isWeekend(): bool
    {
        return in_array((int)$this->format('N'), [6, 7]);
    }

    /**
     * Check if date is a weekday
     *
     * @return bool
     */
    public function isWeekday(): bool
    {
        return !$this->isWeekend();
    }

    /**
     * Convert to ISO 8601 format
     *
     * @return string
     */
    public function toIso8601String(): string
    {
        return $this->format('c');
    }

    /**
     * Convert to date string (Y-m-d)
     *
     * @return string
     */
    public function toDateString(): string
    {
        return $this->format('Y-m-d');
    }

    /**
     * Convert to time string (H:i:s)
     *
     * @return string
     */
    public function toTimeString(): string
    {
        return $this->format('H:i:s');
    }

    /**
     * Convert to datetime string (Y-m-d H:i:s)
     *
     * @return string
     */
    public function toDateTimeString(): string
    {
        return $this->format('Y-m-d H:i:s');
    }

    /**
     * Convert to formatted string
     *
     * @param string $format
     * @return string
     */
    public function formatLocalized(string $format): string
    {
        return $this->format($format);
    }

    /**
     * Get difference in days
     *
     * @param Date|DateTime|null $date
     * @return int
     */
    public function diffInDays(Date|DateTime|null $date = null): int
    {
        $date = $date ?? new static();
        return (int) $this->diff($date)->format('%a');
    }

    /**
     * Get difference in hours
     *
     * @param Date|DateTime|null $date
     * @return int
     */
    public function diffInHours(Date|DateTime|null $date = null): int
    {
        $date = $date ?? new static();
        $diff = $this->getTimestamp() - $date->getTimestamp();
        return (int) floor(abs($diff) / 3600);
    }

    /**
     * Get difference in minutes
     *
     * @param Date|DateTime|null $date
     * @return int
     */
    public function diffInMinutes(Date|DateTime|null $date = null): int
    {
        $date = $date ?? new static();
        $diff = $this->getTimestamp() - $date->getTimestamp();
        return (int) floor(abs($diff) / 60);
    }

    /**
     * Get difference in seconds
     *
     * @param Date|DateTime|null $date
     * @return int
     */
    public function diffInSeconds(Date|DateTime|null $date = null): int
    {
        $date = $date ?? new static();
        return abs($this->getTimestamp() - $date->getTimestamp());
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toDateTimeString();
    }
}
