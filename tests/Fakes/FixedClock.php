<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes;

use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Clock;

final class FixedClock implements Clock
{
    private float $time;

    public function __construct(float $time)
    {
        $this->time = $time;
    }

    public function now(): int
    {
        return (int) floor($this->time);
    }

    public function microtime(): float
    {
        return $this->time;
    }

    public function advance(float $seconds): void
    {
        $this->time += $seconds;
    }
}
