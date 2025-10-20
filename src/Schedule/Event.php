<?php

namespace Nexus\Schedule;

/**
 * Scheduled Event
 *
 * Represents a single scheduled task
 */
class Event
{
    protected $callback;
    protected string $expression = '* * * * *';
    protected ?string $description = null;
    protected bool $withoutOverlapping = false;
    protected ?string $mutex = null;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Set a custom cron expression
     */
    public function cron(string $expression): self
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * Schedule the event to run every minute
     */
    public function everyMinute(): self
    {
        return $this->cron('* * * * *');
    }

    /**
     * Schedule the event to run every five minutes
     */
    public function everyFiveMinutes(): self
    {
        return $this->cron('*/5 * * * *');
    }

    /**
     * Schedule the event to run every ten minutes
     */
    public function everyTenMinutes(): self
    {
        return $this->cron('*/10 * * * *');
    }

    /**
     * Schedule the event to run every fifteen minutes
     */
    public function everyFifteenMinutes(): self
    {
        return $this->cron('*/15 * * * *');
    }

    /**
     * Schedule the event to run every thirty minutes
     */
    public function everyThirtyMinutes(): self
    {
        return $this->cron('*/30 * * * *');
    }

    /**
     * Schedule the event to run hourly
     */
    public function hourly(): self
    {
        return $this->cron('0 * * * *');
    }

    /**
     * Schedule the event to run hourly at a given minute
     */
    public function hourlyAt(int $minute): self
    {
        return $this->cron("{$minute} * * * *");
    }

    /**
     * Schedule the event to run daily
     */
    public function daily(): self
    {
        return $this->cron('0 0 * * *');
    }

    /**
     * Schedule the event to run daily at a specific time
     */
    public function dailyAt(string $time): self
    {
        $segments = explode(':', $time);
        $hour = (int) $segments[0];
        $minute = isset($segments[1]) ? (int) $segments[1] : 0;

        return $this->cron("{$minute} {$hour} * * *");
    }

    /**
     * Schedule the event to run weekly
     */
    public function weekly(): self
    {
        return $this->cron('0 0 * * 0');
    }

    /**
     * Schedule the event to run monthly
     */
    public function monthly(): self
    {
        return $this->cron('0 0 1 * *');
    }

    /**
     * Schedule the event to run yearly
     */
    public function yearly(): self
    {
        return $this->cron('0 0 1 1 *');
    }

    /**
     * Schedule the event to run on weekdays
     */
    public function weekdays(): self
    {
        return $this->cron('* * * * 1-5');
    }

    /**
     * Schedule the event to run on weekends
     */
    public function weekends(): self
    {
        return $this->cron('* * * * 0,6');
    }

    /**
     * Schedule the event to run on Mondays
     */
    public function mondays(): self
    {
        return $this->cron('* * * * 1');
    }

    /**
     * Schedule the event to run on Tuesdays
     */
    public function tuesdays(): self
    {
        return $this->cron('* * * * 2');
    }

    /**
     * Schedule the event to run on Wednesdays
     */
    public function wednesdays(): self
    {
        return $this->cron('* * * * 3');
    }

    /**
     * Schedule the event to run on Thursdays
     */
    public function thursdays(): self
    {
        return $this->cron('* * * * 4');
    }

    /**
     * Schedule the event to run on Fridays
     */
    public function fridays(): self
    {
        return $this->cron('* * * * 5');
    }

    /**
     * Schedule the event to run on Saturdays
     */
    public function saturdays(): self
    {
        return $this->cron('* * * * 6');
    }

    /**
     * Schedule the event to run on Sundays
     */
    public function sundays(): self
    {
        return $this->cron('* * * * 0');
    }

    /**
     * Set event description
     */
    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Prevent the event from overlapping
     */
    public function withoutOverlapping(): self
    {
        $this->withoutOverlapping = true;
        $this->mutex = md5($this->expression . serialize($this->callback));
        return $this;
    }

    /**
     * Determine if the event is due to run
     */
    public function isDue(): bool
    {
        return $this->expressionPasses();
    }

    /**
     * Check if the cron expression passes
     */
    protected function expressionPasses(): bool
    {
        $now = time();
        $parts = explode(' ', $this->expression);

        if (count($parts) !== 5) {
            return false;
        }

        [$minute, $hour, $day, $month, $dayOfWeek] = $parts;

        return $this->matchesPart($minute, (int) date('i', $now)) &&
               $this->matchesPart($hour, (int) date('G', $now)) &&
               $this->matchesPart($day, (int) date('j', $now)) &&
               $this->matchesPart($month, (int) date('n', $now)) &&
               $this->matchesPart($dayOfWeek, (int) date('w', $now));
    }

    /**
     * Check if a part of the cron expression matches
     */
    protected function matchesPart(string $expression, int $value): bool
    {
        // Wildcard
        if ($expression === '*') {
            return true;
        }

        // Step values (e.g., */5)
        if (str_contains($expression, '*/')) {
            $step = (int) str_replace('*/', '', $expression);
            return $value % $step === 0;
        }

        // Range (e.g., 1-5)
        if (str_contains($expression, '-')) {
            [$min, $max] = explode('-', $expression);
            return $value >= (int) $min && $value <= (int) $max;
        }

        // List (e.g., 1,3,5)
        if (str_contains($expression, ',')) {
            $values = array_map('intval', explode(',', $expression));
            return in_array($value, $values);
        }

        // Exact match
        return (int) $expression === $value;
    }

    /**
     * Run the event
     */
    public function run(): void
    {
        if ($this->withoutOverlapping && $this->isLocked()) {
            return;
        }

        if ($this->withoutOverlapping) {
            $this->lock();
        }

        try {
            call_user_func($this->callback);
        } finally {
            if ($this->withoutOverlapping) {
                $this->unlock();
            }
        }
    }

    /**
     * Check if the event is locked
     */
    protected function isLocked(): bool
    {
        if (!$this->mutex) {
            return false;
        }

        $lockFile = $this->getLockPath();
        return file_exists($lockFile);
    }

    /**
     * Lock the event
     */
    protected function lock(): void
    {
        if (!$this->mutex) {
            return;
        }

        $lockFile = $this->getLockPath();
        file_put_contents($lockFile, time());
    }

    /**
     * Unlock the event
     */
    protected function unlock(): void
    {
        if (!$this->mutex) {
            return;
        }

        $lockFile = $this->getLockPath();
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }
    }

    /**
     * Get lock file path
     */
    protected function getLockPath(): string
    {
        return storage_path('framework/schedule/locks/' . $this->mutex);
    }

    /**
     * Get the event description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get the cron expression
     */
    public function getExpression(): string
    {
        return $this->expression;
    }
}
