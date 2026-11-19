<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

/**
 * Moves a website's degrade level after each computation (concept 3.8). Above thresholds the dashboard gets coarser,
 * never the database slower:
 *
 * - level 1: a row cap was hit, or a slow or today run took over 2 s: row caps are halved (more sampling);
 * - level 2: a fast run took over 1 s, or a statement timed out: fast and slow tiers every third interval;
 * - level 3: three timeouts in a row: counters only, every tier waits the backoff (60 s, doubling up to 600 s).
 *
 * After 5 runs under a quarter of the budget the level drops by one.
 */
final class DegradeController
{
    public const GOOD_RUNS_TO_DROP = 5;
    public const TIMEOUTS_FOR_LEVEL3 = 3;
    public const FIRST_BACKOFF = 60;
    public const MAX_BACKOFF = 600;

    public static function after(DegradeState $state, RunOutcome $outcome, Limits $limits): DegradeState
    {
        if ($outcome->timedOut) {
            $timeouts = $state->timeoutsInARow + 1;
            if ($timeouts >= self::TIMEOUTS_FOR_LEVEL3) {
                $backoff = min(self::MAX_BACKOFF, self::FIRST_BACKOFF * 2 ** ($timeouts - self::TIMEOUTS_FOR_LEVEL3));
                return new DegradeState(3, $timeouts, 0, $backoff);
            }
            return new DegradeState(max($state->level, 2), $timeouts, 0, $state->backoffSeconds);
        }

        $raiseTo = 0;
        if ($outcome->capHit || ($outcome->tier !== Tier::Fast && $outcome->seconds > 2.0)) {
            $raiseTo = 1;
        }
        if ($outcome->tier === Tier::Fast && $outcome->seconds > 1.0) {
            $raiseTo = 2;
        }
        if ($raiseTo > 0) {
            return new DegradeState(max($state->level, $raiseTo), 0, 0, $state->backoffSeconds);
        }

        if ($outcome->seconds >= $limits->budgetSeconds / 4) {
            return new DegradeState($state->level, 0, $state->goodRuns, $state->backoffSeconds);
        }

        $goodRuns = $state->goodRuns + 1;
        if ($goodRuns < self::GOOD_RUNS_TO_DROP) {
            return new DegradeState($state->level, 0, $goodRuns, $state->backoffSeconds);
        }
        $level = max(0, $state->level - 1);
        return new DegradeState($level, 0, 0, $level === 3 ? $state->backoffSeconds : 0);
    }
}
