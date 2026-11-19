<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Format;

/**
 * Privacy rules applied to every answer (concept 3.10): visitor-level parts only while the website's visits log is on,
 * and with data rounding (Matomo 5.14+, CNIL mode) every count rounded. Ids, coordinates, revenue, sampling factors and
 * times stay as they are.
 */
final class Privacy
{
    /** "tier.key" of visitor-level panels: only while the website's visits log is on, never with data rounding */
    public const VISITOR_LEVEL = ['fast.newestVisits'];

    /** Counts per tier: a number, or (slow tier) a map list window => number. */
    private const COUNT_KEYS = [
        'activeNow', 'visits5', 'actions5', 'conversions5', 'visits', 'convertedVisits', 'actions', 'pageviews',
        'conversions', 'orders',
    ];

    /** Counts inside rows. */
    private const ROW_COUNT_KEYS = ['count', 'conversions'];

    /**
     * @param callable|null $round fn(int|float): int, null when rounding is off
     */
    public static function apply(array $tiers, bool $visitorLogEnabled, ?callable $round, array $visitorLevelKeys = self::VISITOR_LEVEL): array
    {
        // single visits cannot be rounded: with data rounding on they go, as in Matomo's own real-time widget
        if (!$visitorLogEnabled || $round !== null) {
            foreach ($visitorLevelKeys as $path) {
                [$tier, $key] = explode('.', $path, 2);
                unset($tiers[$tier][$key]);
            }
        }
        if ($round === null) {
            return $tiers;
        }

        foreach ($tiers as $tier => $payload) {
            if (!is_array($payload)) {
                continue;
            }
            foreach ($payload as $key => $value) {
                if (in_array($key, self::COUNT_KEYS, true) && is_numeric($value)) {
                    $tiers[$tier][$key] = $round($value);
                } elseif (in_array($key, self::COUNT_KEYS, true) && is_array($value)) {
                    $tiers[$tier][$key] = array_map(static fn ($n) => is_numeric($n) ? $round($n) : $n, $value);
                } elseif (($key === 'sparkline' || $key === 'visitSparkline') && is_array($value)) {
                    $tiers[$tier][$key] = array_map($round, $value);
                } elseif (is_array($value)) {
                    $tiers[$tier][$key] = self::roundRows($value, $round);
                }
            }
        }
        return $tiers;
    }

    private static function roundRows(array $value, callable $round): array
    {
        foreach ($value as $i => $item) {
            if (!is_array($item)) {
                continue;
            }
            $isRow = false;
            foreach (self::ROW_COUNT_KEYS as $countKey) {
                if (array_key_exists($countKey, $item) && is_numeric($item[$countKey])) {
                    $value[$i][$countKey] = $round($item[$countKey]);
                    $isRow = true;
                }
            }
            if (!$isRow) {
                $value[$i] = self::roundRows($item, $round);
            }
        }
        return $value;
    }
}
