<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Snapshot;

use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;

/**
 * Computes one tier from the log tables.
 */
interface TierComputer
{
    /**
     * @throws \Piwik\Plugins\FeinwerkLiveDashboard\Data\QueryTimeout when a statement hits the time limit
     */
    public function compute(Tier $tier, ComputeContext $ctx): TierResult;
}
