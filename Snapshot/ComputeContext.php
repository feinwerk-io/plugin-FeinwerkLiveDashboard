<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Snapshot;

use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Budget;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Limits;

final class ComputeContext
{
    public function __construct(
        public readonly int $idSite,
        public readonly int $viewId,
        public readonly int $now,
        public readonly string $timezone,
        /** the effective degrade level, the highest of the tiers (caps, cities, counters only) */
        public readonly int $level,
        public readonly Limits $limits,
        public readonly Budget $budget,
        public readonly bool $visitorLogEnabled,
        /** the tier's payload of its last run (the today tier goes on from its counting state) */
        public readonly array $previous = []
    ) {
    }
}
