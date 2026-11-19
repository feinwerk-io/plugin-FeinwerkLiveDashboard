<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Panels;

/**
 * Conversions per list window: per goal, ecommerce orders (idgoal 0) and revenue. Abandoned carts (idgoal -1) are no
 * conversions. Conversions are few, so they are never sampled.
 */
final class ConversionAggregator
{
    public const ORDER = 0;

    /** @var array<string, string> window => from, smallest window first */
    private array $windows = [];
    private array $buckets = [];

    /**
     * @param array<string|int, string> $windows window name => first time it holds; nested, any order
     */
    public function __construct(array $windows)
    {
        arsort($windows, SORT_STRING);
        foreach ($windows as $name => $from) {
            $this->windows[(string) $name] = (string) $from;
            $this->buckets[(string) $name] = self::empty();
        }
    }

    public function add(array $row): void
    {
        $idGoal = (int) $row['idgoal'];
        if ($idGoal < 0) {
            return;
        }
        $time = (string) ($row['server_time'] ?? '');
        foreach ($this->windows as $name => $from) {
            if ($time >= $from) {
                self::count($this->buckets[$name], $idGoal, (float) ($row['revenue'] ?? 0));
                return;
            }
        }
    }

    /** A group counted in the database: [idgoal, w (the window name), conversions, revenue]. */
    public function addGroup(array $row): void
    {
        $idGoal = (int) $row['idgoal'];
        $name = (string) ($row['w'] ?? '');
        if ($idGoal < 0 || !isset($this->buckets[$name])) {
            return;
        }
        self::count($this->buckets[$name], $idGoal, (float) ($row['revenue'] ?? 0), (int) ($row['conversions'] ?? 0));
    }

    public function result(): array
    {
        $result = [];
        $sum = self::empty();
        foreach ($this->buckets as $name => $bucket) {
            $sum['conversions'] += $bucket['conversions'];
            $sum['revenue'] += $bucket['revenue'];
            $sum['orders'] += $bucket['orders'];
            $sum['orderRevenue'] += $bucket['orderRevenue'];
            foreach ($bucket['goals'] as $idGoal => [$count, $revenue]) {
                $sum['goals'][$idGoal][0] = ($sum['goals'][$idGoal][0] ?? 0) + $count;
                $sum['goals'][$idGoal][1] = ($sum['goals'][$idGoal][1] ?? 0.0) + $revenue;
            }
            $goals = [];
            foreach (TopList::top(array_map(static fn (array $g): int => $g[0], $sum['goals']), PHP_INT_MAX) as [$idGoal, $count]) {
                $goals[] = [$idGoal, $count, round($sum['goals'][$idGoal][1], 2)];
            }
            $result[$name] = [
                'conversions' => $sum['conversions'],
                'revenue' => round($sum['revenue'], 2),
                'orders' => $sum['orders'],
                'orderRevenue' => round($sum['orderRevenue'], 2),
                'goals' => $goals,
            ];
        }
        uksort($result, static fn ($a, $b): int => (int) $a <=> (int) $b);
        return $result;
    }

    private static function empty(): array
    {
        return ['conversions' => 0, 'revenue' => 0.0, 'orders' => 0, 'orderRevenue' => 0.0, 'goals' => []];
    }

    private static function count(array &$bucket, int $idGoal, float $revenue, int $n = 1): void
    {
        $bucket['conversions'] += $n;
        $bucket['revenue'] += $revenue;
        if ($idGoal === self::ORDER) {
            $bucket['orders'] += $n;
            $bucket['orderRevenue'] += $revenue;
            return;
        }
        $bucket['goals'][$idGoal][0] = ($bucket['goals'][$idGoal][0] ?? 0) + $n;
        $bucket['goals'][$idGoal][1] = ($bucket['goals'][$idGoal][1] ?? 0.0) + $revenue;
    }
}
