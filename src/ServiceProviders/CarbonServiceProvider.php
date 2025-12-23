<?php

declare(strict_types=1);

namespace Larafony\Clock\Carbon\ServiceProviders;

use Larafony\Clock\Carbon\CarbonClock;
use Larafony\Framework\Clock\ClockFactory;
use Larafony\Framework\Clock\Contracts\Clock;
use Larafony\Framework\Container\Contracts\ContainerContract;
use Larafony\Framework\Container\ServiceProvider;
use Psr\Clock\ClockInterface;

/**
 * Service provider for Carbon clock integration.
 *
 * Registers CarbonClock as the Clock and PSR-20 ClockInterface implementation,
 * replacing the default SystemClock with Carbon-powered clock.
 *
 * Also configures ClockFactory to use Carbon as the default clock,
 * ensuring all code using ClockFactory::instance() gets CarbonClock.
 */
class CarbonServiceProvider extends ServiceProvider
{
    public function providers(): array
    {
        return [
            Clock::class => CarbonClock::class,
            ClockInterface::class => CarbonClock::class,
        ];
    }

    public function register(ContainerContract $container): self
    {
        $clock = CarbonClock::fromTimezone();

        $container->set(Clock::class, $clock);
        $container->set(ClockInterface::class, $clock);
        $container->set(CarbonClock::class, $clock);

        return $this;
    }

    public function boot(ContainerContract $container): void
    {
        parent::boot($container);

        $clock = $container->get(Clock::class);
        ClockFactory::withInstance($clock);
    }
}
