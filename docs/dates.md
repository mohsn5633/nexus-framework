# Date and Time

Nexus Framework provides a powerful Date utility class inspired by Carbon, making it easy to work with dates and times.

## Table of Contents

- [Introduction](#introduction)
- [Creating Dates](#creating-dates)
- [Formatting](#formatting)
- [Manipulation](#manipulation)
- [Comparison](#comparison)
- [Human Readable](#human-readable)
- [Examples](#examples)

## Introduction

The Date class extends PHP's DateTime with an intuitive, fluent API for working with dates and times.

### Features

- **Fluent API**: Chainable methods for easy date manipulation
- **Human Readable**: Convert dates to human-friendly strings
- **Timezone Support**: Easy timezone conversion
- **Comparison**: Compare dates easily
- **Helper Functions**: Convenient helpers for quick access

## Creating Dates

### Current Date and Time

```php
use Nexus\Support\Date;

// Current date and time
$now = Date::now();
$now = now(); // Using helper

// Today at 00:00:00
$today = Date::today();
$today = today(); // Using helper

// Tomorrow at 00:00:00
$tomorrow = Date::tomorrow();

// Yesterday at 00:00:00
$yesterday = Date::yesterday();
```

### Specific Dates

```php
// Create from values
$date = Date::create(2025, 1, 19, 14, 30, 0);

// Parse from string
$date = Date::parse('2025-01-19 14:30:00');
$date = Date::parse('next Monday');
$date = Date::parse('last Friday');

// Using helper
$date = carbon('2025-01-19');
```

### With Timezone

```php
// Create with specific timezone
$date = Date::now('America/New_York');
$date = Date::now(new DateTimeZone('Europe/London'));

// Parse with timezone
$date = Date::parse('2025-01-19', 'Asia/Tokyo');
```

## Formatting

### Common Formats

```php
$date = Date::now();

// ISO 8601
$date->toIso8601String();        // 2025-01-19T14:30:00+00:00

// Date only
$date->toDateString();            // 2025-01-19

// Time only
$date->toTimeString();            // 14:30:00

// Date and time
$date->toDateTimeString();        // 2025-01-19 14:30:00

// Custom format
$date->format('Y-m-d');           // 2025-01-19
$date->format('l, F j, Y');       // Sunday, January 19, 2025
$date->format('h:i A');           // 02:30 PM
```

### String Conversion

```php
$date = now();

// Automatic string conversion
echo $date;                       // 2025-01-19 14:30:00
echo "Today is {$date}";          // Today is 2025-01-19 14:30:00
```

## Manipulation

### Adding Time

```php
$date = now();

// Add days
$date->addDay();                  // Add 1 day
$date->addDays(7);                // Add 7 days

// Add weeks
$date->addWeek();                 // Add 1 week
$date->addWeeks(2);               // Add 2 weeks

// Add months
$date->addMonth();                // Add 1 month
$date->addMonths(3);              // Add 3 months

// Add years
$date->addYear();                 // Add 1 year
$date->addYears(5);               // Add 5 years

// Add hours
$date->addHour();                 // Add 1 hour
$date->addHours(3);               // Add 3 hours

// Add minutes
$date->addMinutes(30);            // Add 30 minutes

// Add seconds
$date->addSeconds(45);            // Add 45 seconds
```

### Subtracting Time

```php
$date = now();

// Subtract days
$date->subDay();                  // Subtract 1 day
$date->subDays(7);                // Subtract 7 days

// Subtract weeks
$date->subWeek();                 // Subtract 1 week
$date->subWeeks(2);               // Subtract 2 weeks

// Subtract months
$date->subMonth();                // Subtract 1 month
$date->subMonths(3);              // Subtract 3 months

// Subtract years
$date->subYear();                 // Subtract 1 year
$date->subYears(5);               // Subtract 5 years

// Subtract hours
$date->subHour();                 // Subtract 1 hour
$date->subHours(3);               // Subtract 3 hours

// Subtract minutes
$date->subMinutes(30);            // Subtract 30 minutes

// Subtract seconds
$date->subSeconds(45);            // Subtract 45 seconds
```

### Chaining

```php
$date = now()
    ->addDays(7)
    ->addHours(3)
    ->addMinutes(30)
    ->startOfDay();
```

### Start and End

```php
$date = now();

// Start of day (00:00:00)
$date->startOfDay();

// End of day (23:59:59)
$date->endOfDay();

// Start of month
$date->startOfMonth();

// End of month
$date->endOfMonth();
```

## Comparison

### Check Date Properties

```php
$date = now();

// Check if past
if ($date->isPast()) {
    echo "This date is in the past";
}

// Check if future
if ($date->isFuture()) {
    echo "This date is in the future";
}

// Check if today
if ($date->isToday()) {
    echo "This date is today";
}

// Check if tomorrow
if ($date->isTomorrow()) {
    echo "This date is tomorrow";
}

// Check if yesterday
if ($date->isYesterday()) {
    echo "This date is yesterday";
}

// Check if weekend
if ($date->isWeekend()) {
    echo "This is a weekend day";
}

// Check if weekday
if ($date->isWeekday()) {
    echo "This is a weekday";
}
```

### Calculate Differences

```php
$start = now();
$end = now()->addDays(30);

// Difference in days
$days = $end->diffInDays($start);           // 30

// Difference in hours
$hours = $end->diffInHours($start);         // 720

// Difference in minutes
$minutes = $end->diffInMinutes($start);     // 43200

// Difference in seconds
$seconds = $end->diffInSeconds($start);     // 2592000

// Difference from now
$days = $end->diffInDays();                 // Days from now
```

## Human Readable

### Diff for Humans

```php
// Past dates
Date::parse('2 hours ago')->diffForHumans();     // 2 hours ago
Date::parse('1 day ago')->diffForHumans();       // 1 day ago
Date::parse('3 weeks ago')->diffForHumans();     // 3 weeks ago

// Future dates
Date::parse('+2 hours')->diffForHumans();        // 2 hours from now
Date::parse('+1 day')->diffForHumans();          // 1 day from now
Date::parse('+3 weeks')->diffForHumans();        // 3 weeks from now

// Examples with now()
now()->subHours(2)->diffForHumans();             // 2 hours ago
now()->addDays(7)->diffForHumans();              // 7 days from now
```

## Examples

### Blog Post Timestamps

```php
<?php

namespace App\Controllers;

use Nexus\Http\Response;
use App\Models\Post;

class BlogController
{
    public function show(int $id): Response
    {
        $post = Post::find($id);

        return Response::view('blog.show', [
            'post' => $post,
            'published' => carbon($post->published_at)->diffForHumans(),
            'updated' => carbon($post->updated_at)->diffForHumans(),
        ]);
    }
}
```

### Event Scheduling

```php
<?php

namespace App\Services;

use Nexus\Support\Date;
use App\Models\Event;

class EventService
{
    public function createEvent(array $data): Event
    {
        return Event::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'starts_at' => now()->addDays(7)->startOfDay()->toDateTimeString(),
            'ends_at' => now()->addDays(7)->endOfDay()->toDateTimeString(),
            'created_at' => now()->toDateTimeString(),
        ]);
    }

    public function getUpcomingEvents(): array
    {
        $events = Event::where('starts_at', '>', now()->toDateTimeString())
            ->orderBy('starts_at', 'ASC')
            ->get();

        return array_map(function ($event) {
            $startsAt = carbon($event['starts_at']);

            return [
                'id' => $event['id'],
                'title' => $event['title'],
                'starts_at' => $startsAt->format('F j, Y \a\t g:i A'),
                'starts_in' => $startsAt->diffForHumans(),
                'is_today' => $startsAt->isToday(),
                'is_tomorrow' => $startsAt->isTomorrow(),
            ];
        }, $events);
    }
}
```

### Subscription Management

```php
<?php

namespace App\Services;

use Nexus\Support\Date;
use App\Models\Subscription;

class SubscriptionService
{
    public function createSubscription(int $userId, string $plan): Subscription
    {
        $duration = match($plan) {
            'monthly' => 1,
            'quarterly' => 3,
            'yearly' => 12,
        };

        return Subscription::create([
            'user_id' => $userId,
            'plan' => $plan,
            'starts_at' => now()->toDateTimeString(),
            'ends_at' => now()->addMonths($duration)->toDateTimeString(),
        ]);
    }

    public function isActive(Subscription $subscription): bool
    {
        $endsAt = carbon($subscription->ends_at);
        return $endsAt->isFuture();
    }

    public function daysRemaining(Subscription $subscription): int
    {
        $endsAt = carbon($subscription->ends_at);
        return max(0, $endsAt->diffInDays());
    }

    public function willExpireSoon(Subscription $subscription): bool
    {
        $endsAt = carbon($subscription->ends_at);
        $daysLeft = $endsAt->diffInDays();

        return $daysLeft <= 7 && $daysLeft > 0;
    }
}
```

### Reporting

```php
<?php

namespace App\Services;

use Nexus\Support\Date;
use App\Models\Order;

class ReportService
{
    public function generateMonthlyReport(int $month, int $year): array
    {
        $startDate = Date::create($year, $month, 1)->startOfMonth();
        $endDate = Date::create($year, $month, 1)->endOfMonth();

        $orders = Order::where('created_at', '>=', $startDate->toDateTimeString())
            ->where('created_at', '<=', $endDate->toDateTimeString())
            ->get();

        return [
            'period' => $startDate->format('F Y'),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_orders' => count($orders),
            'total_revenue' => array_sum(array_column($orders, 'total')),
        ];
    }

    public function getDateRanges(): array
    {
        return [
            'today' => [
                'start' => today()->toDateTimeString(),
                'end' => today()->endOfDay()->toDateTimeString(),
            ],
            'yesterday' => [
                'start' => yesterday()->toDateTimeString(),
                'end' => yesterday()->endOfDay()->toDateTimeString(),
            ],
            'this_week' => [
                'start' => now()->subDays(7)->toDateTimeString(),
                'end' => now()->toDateTimeString(),
            ],
            'this_month' => [
                'start' => now()->startOfMonth()->toDateTimeString(),
                'end' => now()->toDateTimeString(),
            ],
            'last_month' => [
                'start' => now()->subMonth()->startOfMonth()->toDateTimeString(),
                'end' => now()->subMonth()->endOfMonth()->toDateTimeString(),
            ],
        ];
    }
}
```

### Session Expiry

```php
<?php

namespace App\Middleware;

use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Support\Date;

class CheckSessionExpiry extends Middleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        $lastActivity = session('last_activity');

        if ($lastActivity) {
            $lastActivityDate = carbon($lastActivity);

            // Session expires after 30 minutes of inactivity
            if ($lastActivityDate->diffInMinutes() > 30) {
                session()->flush();
                return Response::redirect('/login')
                    ->with('message', 'Your session has expired due to inactivity');
            }
        }

        // Update last activity
        session(['last_activity' => now()->toDateTimeString()]);

        return $next($request);
    }
}
```

### Cached Data Expiry

```php
<?php

namespace App\Services;

use Nexus\Cache\CacheManager;
use Nexus\Support\Date;

class CachedDataService
{
    public function __construct(
        protected CacheManager $cache
    ) {}

    public function getCachedData(string $key): ?array
    {
        $cached = $this->cache->get($key);

        if (!$cached) {
            return null;
        }

        // Check if cached data is stale
        $cachedAt = carbon($cached['cached_at']);

        if ($cachedAt->diffInHours() > 24) {
            // Data is older than 24 hours, invalidate it
            $this->cache->forget($key);
            return null;
        }

        return $cached['data'];
    }

    public function setCachedData(string $key, mixed $data, int $ttl = 3600): void
    {
        $this->cache->put($key, [
            'data' => $data,
            'cached_at' => now()->toDateTimeString(),
        ], $ttl);
    }
}
```

## Best Practices

1. **Use Helpers**: Use `now()`, `today()` helpers for convenience
2. **Timezone Aware**: Always consider timezones in applications
3. **Database Storage**: Store dates in UTC in database
4. **Display Format**: Format dates appropriately for user locale
5. **Validation**: Validate date inputs before processing
6. **Immutability**: Create new instances instead of modifying existing ones
7. **Human Readable**: Use `diffForHumans()` for user-facing dates

## Common Patterns

### Age Calculation

```php
$birthDate = Date::parse('1990-05-15');
$age = $birthDate->diffInYears(now());
```

### Business Days

```php
$date = now();
$businessDays = 0;

while ($businessDays < 5) {
    $date = $date->addDay();
    if ($date->isWeekday()) {
        $businessDays++;
    }
}
```

### Date Range

```php
$start = now()->startOfMonth();
$end = now()->endOfMonth();

$dates = [];
$current = $start;

while ($current <= $end) {
    $dates[] = $current->toDateString();
    $current = $current->addDay();
}
```

## Next Steps

- Learn about [Models](models.md)
- Understand [Validation](validation.md)
- Explore [Helpers](helpers.md)
