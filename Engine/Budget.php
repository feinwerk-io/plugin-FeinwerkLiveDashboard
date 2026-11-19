<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

/**
 * Wall-clock budget of one snapshot computation (concept 3.7): reads that would start after it are skipped and the
 * panels keep their previous values with their age.
 */
final class Budget
{
    private float $start;

    public function __construct(private readonly Clock $clock, private readonly float $seconds)
    {
        $this->start = $clock->microtime();
    }

    public function elapsed(): float
    {
        return $this->clock->microtime() - $this->start;
    }

    public function remaining(): float
    {
        return max(0.0, $this->seconds - $this->elapsed());
    }

    public function exhausted(): bool
    {
        return $this->remaining() <= 0.0;
    }
}
