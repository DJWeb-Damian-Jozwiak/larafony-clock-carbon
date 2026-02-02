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

$app->withServiceProviders([
    CarbonServiceProvider::class
]);
```

### Basic operations

```php
use Larafony\Clock\Carbon\CarbonClock;
use Larafony\Framework\Web\Application;
use Larafony\Framework\Web\Controller;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Larafony\Framework\Http\Factories\ResponseFactory;

final class SomeController extends Controller
{
    #[Route('/some-route')]
    //auto inject from DI container
    public function someAction(CarbonClock $clock): \Psr\Http\Message\ResponseInterface
    {
        //get from application singleton
        $clock2 = Application::get(CarbonClock::class);
        return ResponseFactory::createJsonResponse(
            [
                'now' => $clock->now(),
                'diff' => $clock->now()->diffForHumans()
                'long_day' => $clock->addDays(5)->format('l')
            ]
        );
    }
}

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

MIT License. Larafony-clock-carbon is open-sourced software licensed under the [MIT license](https://opensource.org/license/MIT).
