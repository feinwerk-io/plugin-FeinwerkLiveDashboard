<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeState;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeStates;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Limits;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Schedule;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;

/**
 * Each tier keeps its own degrade state; the board's level is the highest of fast and slow and slows them down; the
 * today tier only slows itself.
 *
 * @group FeinwerkLiveDashboard
 */
class ScheduleTest extends TestCase
{
    private static function states(array $levels): DegradeStates
    {
        $states = new DegradeStates();
        foreach ($levels as $tier => $state) {
            $states = $states->with(Tier::from($tier), is_int($state) ? new DegradeState($state) : $state);
        }
        return $states;
    }

    public function test_levels0And1_useTheLimits(): void
    {
        foreach ([0, 1] as $level) {
            $s = self::states(['slow' => $level]);
            $this->assertSame(10, Schedule::interval(Tier::Fast, $s, new Limits()));
            $this->assertSame(60, Schedule::interval(Tier::Slow, $s, new Limits()));
            $this->assertSame(300, Schedule::interval(Tier::Today, $s, new Limits()));
        }
    }

    public function test_level2OfAnyTier_triplesFastAndSlow(): void
    {
        $s = self::states(['fast' => 2]);
        $this->assertSame(2, $s->level());
        $this->assertSame(30, Schedule::interval(Tier::Fast, $s, new Limits()));
        $this->assertSame(180, Schedule::interval(Tier::Slow, $s, new Limits()));
    }

    public function test_level2_keepsTheTodayCountersWithTheirAge(): void
    {
        // the today counters are the biggest single read (concept 3.11); from level 2 they wait
        $this->assertTrue(Schedule::computes(Tier::Today, self::states(['slow' => 1])));
        $this->assertFalse(Schedule::computes(Tier::Today, self::states(['slow' => 2])));
        $this->assertTrue(Schedule::computes(Tier::Slow, self::states(['fast' => 2])));
    }

    public function test_level3_computesOnlyFastCounters(): void
    {
        $s = self::states(['fast' => new DegradeState(3, 3, 0, 240)]);
        $this->assertTrue(Schedule::computes(Tier::Fast, $s));
        $this->assertFalse(Schedule::computes(Tier::Slow, $s));
        $this->assertFalse(Schedule::computes(Tier::Today, $s));
        $this->assertSame(240, Schedule::interval(Tier::Fast, $s, new Limits()));
    }

    public function test_aTierAtLevel3_retriesAfterItsBackoff(): void
    {
        // otherwise a tier that timed out three times would never run again and never recover
        $s = self::states(['slow' => new DegradeState(3, 3, 0, 240)]);
        $this->assertTrue(Schedule::computes(Tier::Slow, $s));
        $this->assertSame(240, Schedule::interval(Tier::Slow, $s, new Limits()));
        $this->assertTrue(Schedule::computes(Tier::Fast, $s));
        $this->assertSame(30, Schedule::interval(Tier::Fast, $s, new Limits()), 'the others wait as at level 2');
        $this->assertFalse(Schedule::computes(Tier::Today, $s));
        $this->assertSame(180, Schedule::interval(Tier::Slow, self::states(['slow' => new DegradeState(3)]), new Limits()));
    }

    public function test_aTierThatRaisedLevel2_keepsRunningToRecover(): void
    {
        $s = self::states(['today' => 2]);
        $this->assertTrue(Schedule::computes(Tier::Today, $s));
        $this->assertSame(300, Schedule::interval(Tier::Today, $s, new Limits()));
    }

    public function test_level3Backoff_isNeverShorterThanTheLevel2Interval(): void
    {
        $this->assertSame(180, Schedule::interval(Tier::Slow, self::states(['slow' => new DegradeState(3, 3, 0, 60)]), new Limits()));
        $this->assertSame(300, Schedule::interval(Tier::Today, self::states(['today' => new DegradeState(3, 3, 0, 120)]), new Limits()));
        $this->assertSame(600, Schedule::interval(Tier::Slow, self::states(['slow' => new DegradeState(3, 6, 0, 600)]), new Limits()));
        $this->assertSame(60, Schedule::interval(Tier::Fast, self::states(['fast' => new DegradeState(3, 3, 0, 60)]), new Limits()));
    }

    public function test_slowTodayCounters_neverDegradeTheRestOfTheBoard(): void
    {
        // the today read grows all day; its timeouts only slow the today counters down, never the lists, the map or
        // the live log (found in the release review 2026-11-18: one tier stuck at level 3 until after midnight)
        $s = self::states(['today' => new DegradeState(3, 3, 0, 240)]);
        $this->assertSame(0, $s->level());
        $this->assertTrue(Schedule::computes(Tier::Slow, $s));
        $this->assertSame(10, Schedule::interval(Tier::Fast, $s, new Limits()));
        $this->assertSame(60, Schedule::interval(Tier::Slow, $s, new Limits()));
        $this->assertSame(300, Schedule::interval(Tier::Today, $s, new Limits()), 'its own backoff, at least its level 2 interval');
        $this->assertTrue(Schedule::computes(Tier::Today, $s), 'it keeps retrying to recover');
    }

    public function test_effectiveCap_isHalvedFromLevel1(): void
    {
        $this->assertSame(100000, Schedule::effectiveCap(100000, 0));
        $this->assertSame(50000, Schedule::effectiveCap(100000, 1));
        $this->assertSame(50000, Schedule::effectiveCap(100000, 2));
        $this->assertSame(1, Schedule::effectiveCap(1, 2));
    }

    public function test_states_roundTripAndTolerateBrokenData(): void
    {
        $s = self::states(['fast' => new DegradeState(2, 1, 0, 0), 'today' => new DegradeState(1)]);
        $this->assertEquals($s, DegradeStates::fromArray($s->toArray()));
        $this->assertSame(0, DegradeStates::fromArray(null)->level());
        $this->assertSame(0, DegradeStates::fromArray(['level' => 2])->level(), 'an unknown layout counts as level 0');
        $this->assertSame(1, DegradeStates::fromArray(['fast' => ['level' => 1], 'nonsense' => ['level' => 3]])->level());
        $this->assertEquals(new DegradeState(), (new DegradeStates())->get(Tier::Slow));
    }
}
