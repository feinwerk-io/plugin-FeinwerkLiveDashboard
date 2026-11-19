<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

/**
 * Refresh interval and work of each tier per degrade level (concept 3.4 and 3.8).
 */
final class Schedule
{
    public static function interval(Tier $tier, DegradeStates $states, Limits $limits): int
    {
        $base = match ($tier) {
            Tier::Fast => $limits->fastSeconds,
            Tier::Slow => $limits->slowSeconds,
            Tier::Today => $limits->todaySeconds,
        };
        $slowed = $tier === Tier::Today ? $base : 3 * $base;
        $own = $states->get($tier);
        if ($own->level >= 3) {
            // the backoff, never shorter than the tier's level 2 interval
            return max(DegradeController::FIRST_BACKOFF, $own->backoffSeconds, $slowed);
        }
        // the board's level, or the tier's own (the today tier is not part of the board's level)
        return max($states->level(), $own->level) >= 2 ? $slowed : $base;
    }

    /**
     * From the effective level 2 the today counters (the biggest single read) keep their values with their age; at level
     * 3 only the fast counters run. A tier that raised the level to 2 or 3 itself keeps running at its interval or
     * backoff, so its own runs can lower its level again.
     */
    public static function computes(Tier $tier, DegradeStates $states): bool
    {
        if ($states->get($tier)->level >= 2) {
            return true;
        }
        $level = $states->level();
        return match ($tier) {
            Tier::Fast => true,
            Tier::Slow => $level < 3,
            Tier::Today => $level < 2,
        };
    }

    /** From level 1 the row caps are halved, so more windows are sampled. */
    public static function effectiveCap(int $cap, int $level): int
    {
        return $level >= 1 ? max(1, intdiv($cap, 2)) : $cap;
    }
}
