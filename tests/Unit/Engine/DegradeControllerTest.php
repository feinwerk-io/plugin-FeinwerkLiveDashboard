<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeController;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeState;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Limits;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\RunOutcome;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;

/**
 * Degrade levels of concept 3.8 (budget 8 s, so a good run is under 2 s).
 *
 * @group FeinwerkLiveDashboard
 */
class DegradeControllerTest extends TestCase
{
    private static function after(DegradeState $state, Tier $tier, float $seconds, bool $timedOut = false, bool $capHit = false): DegradeState
    {
        return DegradeController::after($state, new RunOutcome($tier, $seconds, $timedOut, $capHit), new Limits());
    }

    public function test_quickRun_staysAtLevel0(): void
    {
        $s = self::after(new DegradeState(), Tier::Fast, 0.05);
        $this->assertSame(0, $s->level);
        $this->assertSame(1, $s->goodRuns);
    }

    public function test_capHit_givesAtLeastLevel1(): void
    {
        $s = self::after(new DegradeState(), Tier::Slow, 0.3, false, true);
        $this->assertSame(1, $s->level);
        $this->assertSame(0, $s->goodRuns);
    }

    public function test_slowRunOver2Seconds_givesLevel1(): void
    {
        $this->assertSame(1, self::after(new DegradeState(), Tier::Slow, 2.1)->level);
        $this->assertSame(1, self::after(new DegradeState(), Tier::Today, 2.1)->level);
    }

    public function test_fastRunOver1Second_givesLevel2(): void
    {
        $this->assertSame(2, self::after(new DegradeState(), Tier::Fast, 1.1)->level);
    }

    public function test_timeout_givesLevel2(): void
    {
        $s = self::after(new DegradeState(), Tier::Slow, 3.0, true);
        $this->assertSame(2, $s->level);
        $this->assertSame(1, $s->timeoutsInARow);
    }

    public function test_threeTimeoutsInARow_giveLevel3WithBackoff60(): void
    {
        $s = new DegradeState();
        for ($i = 0; $i < 3; $i++) {
            $s = self::after($s, Tier::Fast, 3.0, true);
        }
        $this->assertSame(3, $s->level);
        $this->assertSame(60, $s->backoffSeconds);
    }

    public function test_backoffDoublesUpTo600(): void
    {
        $s = new DegradeState();
        $backoffs = [];
        for ($i = 0; $i < 8; $i++) {
            $s = self::after($s, Tier::Fast, 3.0, true);
            $backoffs[] = $s->backoffSeconds;
        }
        $this->assertSame([0, 0, 60, 120, 240, 480, 600, 600], $backoffs);
    }

    public function test_aRunWithoutTimeout_resetsTheTimeoutsInARow(): void
    {
        $s = self::after(self::after(new DegradeState(), Tier::Fast, 3.0, true), Tier::Fast, 0.1);
        $this->assertSame(0, $s->timeoutsInARow);
        $this->assertSame(2, $s->level);
    }

    public function test_fiveGoodRuns_dropOneLevel(): void
    {
        $s = new DegradeState(2);
        for ($i = 0; $i < 4; $i++) {
            $s = self::after($s, Tier::Fast, 0.1);
        }
        $this->assertSame(2, $s->level);
        $s = self::after($s, Tier::Fast, 0.1);
        $this->assertSame(1, $s->level);
        $this->assertSame(0, $s->goodRuns);
    }

    public function test_droppingBelowLevel3_clearsTheBackoff(): void
    {
        $s = new DegradeState(3, 0, 4, 240);
        $s = self::after($s, Tier::Fast, 0.1);
        $this->assertSame(2, $s->level);
        $this->assertSame(0, $s->backoffSeconds);
    }

    public function test_badRun_resetsTheGoodRuns(): void
    {
        $s = self::after(new DegradeState(1, 0, 4, 0), Tier::Slow, 0.2, false, true);
        $this->assertSame(1, $s->level);
        $this->assertSame(0, $s->goodRuns);
    }

    public function test_level0_neverGoesBelowZero(): void
    {
        $s = new DegradeState();
        for ($i = 0; $i < 12; $i++) {
            $s = self::after($s, Tier::Fast, 0.1);
        }
        $this->assertSame(0, $s->level);
    }

    public function test_state_roundTripsThroughArrays(): void
    {
        $s = new DegradeState(3, 4, 1, 120);
        $this->assertEquals($s, DegradeState::fromArray($s->toArray()));
    }

    public function test_brokenStateArray_givesLevel0(): void
    {
        $this->assertEquals(new DegradeState(), DegradeState::fromArray(null));
        $this->assertEquals(new DegradeState(), DegradeState::fromArray(['level' => 'x']));
        $this->assertSame(3, DegradeState::fromArray(['level' => 9])->level);
        $this->assertSame(0, DegradeState::fromArray(['level' => -2])->level);
    }
}
