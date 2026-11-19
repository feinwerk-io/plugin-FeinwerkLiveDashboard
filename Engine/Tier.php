<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

/**
 * The three refresh tiers of a snapshot (concept 3.4): fast (counters, pages viewed now), slow (30 minute lists),
 * today (counters since local midnight). Each tier is its own snapshot row and its own lock.
 */
enum Tier: string
{
    case Fast = 'fast';
    case Slow = 'slow';
    case Today = 'today';

    /** @return Tier[] in the order a poll looks for a stale tier */
    public static function ordered(): array
    {
        return [self::Fast, self::Slow, self::Today];
    }
}
