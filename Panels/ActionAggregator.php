<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Panels;

/**
 * One pass over the hits of a read (concept 3.4). Events (a category is set) count by category and action; hits with a
 * URL count by URL id and title id, which the computer later splits into pages, downloads and outlinks by
 * log_action.type; hits without a URL but with a name are site search keyword candidates. Every hit counts as an action
 * (Matomo's "actions" of its real-time counters) and in a per-minute sparkline. Like VisitAggregator, a hit goes into
 * the smallest list window that holds it and the larger windows are summed at the end.
 */
final class ActionAggregator
{
    private const MAP_KEYS = ['urls', 'titles', 'searchNames', 'eventCategories', 'eventActions'];

    /** @var array<string, string> window => from, smallest window first */
    private array $windows = [];
    private array $buckets = [];
    private int $actions = 0;
    /** @var array<string, int> 'Y-m-d H:i' => hits */
    private array $minutes = [];

    /**
     * @param array<string|int, string> $windows window name => first time it holds; nested, any order
     */
    public function __construct(array $windows, private readonly int $end, private readonly int $k, private readonly int $sparkMinutes = 60)
    {
        arsort($windows, SORT_STRING);
        foreach ($windows as $name => $from) {
            $this->windows[(string) $name] = (string) $from;
            $this->buckets[(string) $name] = ['actions' => 0] + array_fill_keys(self::MAP_KEYS, []);
        }
    }

    public function add(array $row): void
    {
        $time = (string) $row['server_time'];
        $this->actions++;
        $minute = substr($time, 0, 16);
        $this->minutes[$minute] = ($this->minutes[$minute] ?? 0) + 1;

        $window = null;
        foreach ($this->windows as $name => $from) {
            if ($time >= $from) {
                $window = $name;
                break;
            }
        }
        if ($window === null) {
            return;
        }
        $bucket = &$this->buckets[$window];
        $bucket['actions']++;

        if (!empty($row['idaction_event_category'])) {
            self::inc($bucket['eventCategories'], (int) $row['idaction_event_category']);
            if (!empty($row['idaction_event_action'])) {
                self::inc($bucket['eventActions'], (int) $row['idaction_event_action']);
            }
            return;
        }

        $name = (int) ($row['idaction_name'] ?? 0);
        if (empty($row['idaction_url'])) {
            if ($name > 0) {
                self::inc($bucket['searchNames'], $name);
            }
            return;
        }
        self::inc($bucket['urls'], (int) $row['idaction_url']);
        if ($name > 0) {
            self::inc($bucket['titles'], $name);
        }
    }

    public function result(): array
    {
        $k = max(1, $this->k);
        $lastMinute = intdiv($this->end, 60) * 60;
        $sparkline = [];
        for ($i = $this->sparkMinutes - 1; $i >= 0; $i--) {
            $sparkline[] = ($this->minutes[gmdate('Y-m-d H:i', $lastMinute - $i * 60)] ?? 0) * $k;
        }

        $windows = [];
        $sum = ['actions' => 0] + array_fill_keys(self::MAP_KEYS, []);
        foreach ($this->buckets as $name => $bucket) {
            $sum['actions'] += $bucket['actions'];
            $window = ['actions' => $sum['actions'] * $k];
            foreach (self::MAP_KEYS as $key) {
                foreach ($bucket[$key] as $id => $count) {
                    $sum[$key][$id] = ($sum[$key][$id] ?? 0) + $count;
                }
                $window[$key] = self::scale($sum[$key], $k);
            }
            $windows[$name] = $window;
        }
        uksort($windows, static fn ($a, $b): int => (int) $a <=> (int) $b);

        return ['actions' => $this->actions * $k, 'sparkline' => $sparkline, 'windows' => $windows];
    }

    private static function inc(array &$map, int $key): void
    {
        $map[$key] = ($map[$key] ?? 0) + 1;
    }

    private static function scale(array $map, int $k): array
    {
        return $k === 1 ? $map : array_map(static fn (int $n): int => $n * $k, $map);
    }
}
