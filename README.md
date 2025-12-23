# Larafony Carbon Bridge

This package provides integration between Larafony Framework and [Carbon](https://carbon.nesbot.com/) - the popular PHP date/time library.

## Installation

```bash
composer require larafony/clock-carbon
```

## Usage

Register the service provider in your `bootstrap.php`:

```php
use Larafony\Clock\Carbon\ServiceProviders\CarbonServiceProvider;

$app->register(CarbonServiceProvider::class);
```

### Basic operations

```php
use Larafony\Clock\Carbon\CarbonClock;

// Get from container
$clock = $container->get(CarbonClock::class);

// Get current time
$now = $clock->now();

// Carbon provides rich date/time manipulation
echo $now->format('Y-m-d H:i:s');
echo $now->diffForHumans(); // "just now"
echo $now->addDays(5)->format('l'); // "Saturday"
```

### PSR-20 Compatibility

The Carbon bridge implements PSR-20 `ClockInterface`, making it a drop-in replacement for Larafony's built-in clock:

```php
use Psr\Clock\ClockInterface;

// Works with any PSR-20 compatible code
function doSomething(ClockInterface $clock): void
{
    $now = $clock->now();
    // ...
}
```

## Features

- **PSR-20 compatible** - Implements `ClockInterface`
- **Full Carbon API** - Access all Carbon features
- **Timezone support** - Easy timezone manipulation
- **Human-readable dates** - `diffForHumans()` and more
- **Immutable by default** - CarbonImmutable for safety

## Why use this bridge?

While Larafony includes a built-in PSR-20 clock implementation, Carbon offers:

- Rich date/time manipulation API
- Human-readable date differences
- Localization support for 100+ languages
- Date comparison and testing utilities
- Battle-tested codebase used by millions

## Learn How It's Built - From Scratch

Interested in **how Larafony is built step by step?**

Check out my full PHP 8.5 course, where I explain everything from architecture to implementation - no magic, just clean code.

Get it now at [masterphp.eu](https://masterphp.eu)

## License

MIT
