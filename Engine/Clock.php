<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

/**
 * The time source of the engine. Injected everywhere, so tests run on a fixed "now" and no statement uses SQL NOW().
 */
interface Clock
{
    /** Unix seconds. */
    public function now(): int;

    /** Unix seconds with fractions, for budgets and run times. */
    public function microtime(): float;
}
