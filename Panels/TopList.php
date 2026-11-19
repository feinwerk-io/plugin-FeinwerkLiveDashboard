<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Panels;

final class TopList
{
    /**
     * @param array<int|string, int|float> $counts
     * @return array<int, array{0: int|string, 1: int|float}> [key, count], count descending, then key ascending
     */
    public static function top(array $counts, int $n): array
    {
        if ($n <= 0 || $counts === []) {
            return [];
        }
        $rows = [];
        foreach ($counts as $key => $count) {
            $rows[] = [$key, $count];
        }
        usort($rows, static function (array $a, array $b): int {
            if ($a[1] !== $b[1]) {
                return $b[1] <=> $a[1];
            }
            return is_int($a[0]) && is_int($b[0]) ? $a[0] <=> $b[0] : strcmp((string) $a[0], (string) $b[0]);
        });
        return array_slice($rows, 0, $n);
    }
}
