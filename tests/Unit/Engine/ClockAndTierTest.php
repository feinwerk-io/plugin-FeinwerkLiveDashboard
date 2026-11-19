<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\SystemClock;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes\FixedClock;

/**
 * @group FeinwerkLiveDashboard
 */
class ClockAndTierTest extends TestCase
{
    public function test_systemClock_tellsTheTime(): void
    {
        $before = time();
        $clock = new SystemClock();
        $now = $clock->now();
        $this->assertGreaterThanOrEqual($before, $now);
        $this->assertLessThanOrEqual(time(), $now);
        $this->assertEqualsWithDelta(microtime(true), $clock->microtime(), 5.0);
    }

    public function test_fixedClock_advances(): void
    {
        $clock = new FixedClock(1000);
        $clock->advance(2.5);
        $this->assertSame(1002, $clock->now());
        $this->assertSame(1002.5, $clock->microtime());
    }

    public function test_tiers_areOrderedFastSlowToday(): void
    {
        $this->assertSame([Tier::Fast, Tier::Slow, Tier::Today], Tier::ordered());
        $this->assertSame('fast', Tier::Fast->value);
        $this->assertSame(Tier::Slow, Tier::tryFrom('slow'));
    }
}
