<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

/**
 * Sampling above the row caps (concept 3.7): a window with more rows than the cap is read as every k-th row
 * (`idvisit % k = 0`, `idlink_va % k = 0`), and counts are scaled by k.
 */
final class Sampling
{
    public static function factor(int $count, int $cap): int
    {
        $cap = max(1, $cap);
        return $count <= $cap ? 1 : (int) ceil($count / $cap);
    }
}
