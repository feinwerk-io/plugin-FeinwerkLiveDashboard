<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

/**
 * Limits of one request, read once (LimitsProvider). Defaults from concept 3.4 and 3.7.
 */
final class Limits
{
    public function __construct(
        public readonly int $fastSeconds = 10,
        public readonly int $slowSeconds = 60,
        public readonly int $todaySeconds = 300,
        public readonly int $statementSeconds = 3,
        public readonly int $budgetSeconds = 8,
        public readonly int $visitRowCap = 50000,
        public readonly int $actionRowCap = 100000,
        public readonly int $maxConcurrent = 2,
        public readonly int $maxActiveKeys = 20,
        public readonly int $cityMinVisits = 3,
        public readonly int $activeMinutes = 3,
        public readonly int $typeLookupCap = 20000,
        public readonly bool $useReader = true
    ) {
    }
}
