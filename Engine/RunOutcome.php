<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

/**
 * What one tier computation took: its wall time, whether a statement hit the time limit, whether a row cap was hit.
 */
final class RunOutcome
{
    public function __construct(
        public readonly Tier $tier,
        public readonly float $seconds,
        public readonly bool $timedOut,
        public readonly bool $capHit
    ) {
    }
}
