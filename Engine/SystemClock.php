<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

final class SystemClock implements Clock
{
    public function now(): int
    {
        return time();
    }

    public function microtime(): float
    {
        return microtime(true);
    }
}
