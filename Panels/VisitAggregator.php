<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Panels;

/**
 * One pass over the visit rows of a read (concept 3.4). Every visit counts for the read's total; for the list windows
 * (for example 3, 10, 30 and 60 minutes, nested) a visit goes into the smallest window that holds it, and the larger
 * windows are summed up at the end, so each row costs one bucket whatever the number of windows. Visits since
 * activeFrom count as "active now" with their exit page ("pages viewed now"). With a sparkline, every visit also
 * counts in the minute of its first action (new visits per minute). Times are compared as 'Y-m-d H:i:s'
 * strings, which sort like the times they hold. Sampled rows count k times.
 */
final class VisitAggregator
{
    public const UNKNOWN_COUNTRY = 'xx';
    public const UNKNOWN_REFERRER_TYPE = 0;
    public const UNKNOWN_DEVICE = -1;
    public const CAMPAIGN = 6;

    private const TOP_PAGES = 50;
    private const TOP_CAMPAIGNS = 50;
    private const TOP_CITIES = 100;
    private const TOP_REGIONS = 300;
    private const BUCKET_KEYS = ['channels', 'campaigns', 'countries', 'devices'];

    /** @var array<string, string> window => from, smallest window first */
    private array $windows;
    private int $visits = 0;
    private int $active = 0;
    private array $pagesNowUrls = [];
    private array $pagesNowTitles = [];
    private array $channelsNow = [];
    private array $countriesNow = [];
    /** @var array<string, array> window => counts of the visits only this window (not a smaller one) holds */
    private array $buckets = [];
    /** @var array<string, array{0: string, 1: string, 2: string, 3: float, 4: float}> city key => country, region, city, lat, lon */
    private array $cityPlaces = [];
    /** @var array<string, int> 'Y-m-d H:i' of the first action => visits */
    private array $starts = [];

    /**
     * @param array<string|int, string> $windows window name => first time it holds; nested, any order
     */
    public function __construct(
        private readonly string $activeFrom,
        array $windows,
        private readonly int $k,
        private readonly bool $withCities,
        private readonly int $cityMinVisits,
        private readonly ?int $sparkEnd = null,
        private readonly int $sparkMinutes = 0,
        private readonly bool $withCampaignSources = false
    ) {
        arsort($windows, SORT_STRING); // latest start = smallest window first
        $this->windows = [];
        foreach ($windows as $name => $from) {
            $this->windows[(string) $name] = (string) $from;
            $this->buckets[(string) $name] = ['visits' => 0, 'convertedVisits' => 0, 'campaignSources' => [], 'cities' => [], 'regions' => []]
                + array_fill_keys(self::BUCKET_KEYS, []);
        }
    }

    public function add(array $row): void
    {
        $time = (string) $row['visit_last_action_time'];
        $type = isset($row['referer_type']) ? (int) $row['referer_type'] : self::UNKNOWN_REFERRER_TYPE;
        $country = (string) ($row['location_country'] ?? '');
        $country = $country === '' ? self::UNKNOWN_COUNTRY : $country;
        $this->visits++;
        if ($this->sparkMinutes > 0 && isset($row['visit_first_action_time'])) {
            $minute = substr((string) $row['visit_first_action_time'], 0, 16);
            $this->starts[$minute] = ($this->starts[$minute] ?? 0) + 1;
        }

        // the region is read with the cities: counted for every visit that has one, with or without coordinates
        $regionKey = null;
        if ($this->withCities && (string) ($row['location_region'] ?? '') !== '') {
            $regionKey = $country . '|' . $row['location_region'];
        }
        $cityKey = null;
        if ($this->withCities && isset($row['lat'], $row['lon'])) {
            $region = (string) ($row['location_region'] ?? '');
            $city = (string) ($row['location_city'] ?? '');
            $cityKey = $country . '|' . $region . '|' . $city;
            $this->cityPlaces[$cityKey] ??= [$country, $region, $city, (float) $row['lat'], (float) $row['lon']];
        }

        foreach ($this->windows as $name => $from) {
            if ($time >= $from) {
                $bucket = &$this->buckets[$name];
                $bucket['visits']++;
                if ($cityKey !== null) {
                    self::inc($bucket['cities'], $cityKey);
                }
                if ($regionKey !== null) {
                    self::inc($bucket['regions'], $regionKey);
                }
                if (!empty($row['visit_goal_converted'])) {
                    $bucket['convertedVisits']++;
                }
                self::inc($bucket['channels'], $type);
                self::inc($bucket['countries'], $country);
                self::inc($bucket['devices'], isset($row['config_device_type']) ? (int) $row['config_device_type'] : self::UNKNOWN_DEVICE);
                if ($type === self::CAMPAIGN) {
                    self::inc($bucket['campaigns'], (string) ($row['referer_name'] ?? ''));
                    $source = (string) ($row['campaign_source'] ?? '');
                    if ($this->withCampaignSources && $source !== '') {
                        self::inc($bucket['campaignSources'], $source);
                    }
                }
                unset($bucket);
                break;
            }
        }

        if ($time >= $this->activeFrom) {
            $this->active++;
            self::inc($this->pagesNowUrls, (int) ($row['visit_exit_idaction_url'] ?? 0));
            self::inc($this->pagesNowTitles, (int) ($row['visit_exit_idaction_name'] ?? 0));
            self::inc($this->channelsNow, $type);
            self::inc($this->countriesNow, $country);
        }
    }

    public function result(): array
    {
        $k = max(1, $this->k);
        $windows = [];
        $sum = ['visits' => 0, 'convertedVisits' => 0, 'campaignSources' => [], 'cities' => [], 'regions' => []]
            + array_fill_keys(self::BUCKET_KEYS, []);
        foreach ($this->buckets as $name => $bucket) {
            $sum['visits'] += $bucket['visits'];
            $sum['convertedVisits'] += $bucket['convertedVisits'];
            foreach (array_merge(self::BUCKET_KEYS, ['campaignSources', 'cities', 'regions']) as $key) {
                foreach ($bucket[$key] as $label => $count) {
                    $sum[$key][$label] = ($sum[$key][$label] ?? 0) + $count;
                }
            }
            $windows[$name] = [
                'visits' => $sum['visits'] * $k,
                'convertedVisits' => $sum['convertedVisits'] * $k,
                'channels' => TopList::top(self::scale($sum['channels'], $k), PHP_INT_MAX),
                'campaigns' => TopList::top(self::scale($sum['campaigns'], $k), self::TOP_CAMPAIGNS),
                'countries' => TopList::top(self::scale($sum['countries'], $k), PHP_INT_MAX),
                'devices' => TopList::top(self::scale($sum['devices'], $k), PHP_INT_MAX),
                'cities' => $this->cities($sum['cities'], $k),
                'regions' => self::regions($sum['regions'], $k),
            ];
            if ($this->withCampaignSources) {
                $windows[$name]['campaignSources'] = TopList::top(self::scale($sum['campaignSources'], $k), self::TOP_CAMPAIGNS);
            }
        }
        uksort($windows, static fn ($a, $b): int => (int) $a <=> (int) $b);

        $visitSparkline = [];
        if ($this->sparkMinutes > 0 && $this->sparkEnd !== null) {
            $lastMinute = intdiv($this->sparkEnd, 60) * 60;
            for ($i = $this->sparkMinutes - 1; $i >= 0; $i--) {
                $visitSparkline[] = ($this->starts[gmdate('Y-m-d H:i', $lastMinute - $i * 60)] ?? 0) * $k;
            }
        }

        return [
            'visits' => $this->visits * $k,
            'visitSparkline' => $visitSparkline,
            'activeNow' => $this->active * $k,
            'pagesNowUrls' => TopList::top(self::scale($this->pagesNowUrls, $k), self::TOP_PAGES),
            'pagesNowTitles' => TopList::top(self::scale($this->pagesNowTitles, $k), self::TOP_PAGES),
            'channelsNow' => TopList::top(self::scale($this->channelsNow, $k), PHP_INT_MAX),
            'countriesNow' => TopList::top(self::scale($this->countriesNow, $k), PHP_INT_MAX),
            'windows' => $windows,
        ];
    }

    /**
     * The cities of one window: at least city_min_visits visits each (a privacy floor, per window), most visits first.
     *
     * @param array<string, int> $counts city key => visits
     * @return array<int, array{0: string, 1: string, 2: string, 3: float, 4: float, 5: int}>
     */
    private function cities(array $counts, int $k): array
    {
        $cities = [];
        foreach ($counts as $key => $count) {
            // the floor counts the visits seen, not the estimate: one sampled visit never shows its town
            if ($count >= $this->cityMinVisits) {
                $cities[] = [...$this->cityPlaces[$key], $count * $k];
            }
        }
        usort($cities, static fn (array $a, array $b): int => [$b[5], $a[0] . $a[2]] <=> [$a[5], $b[0] . $b[2]]);
        return array_slice($cities, 0, self::TOP_CITIES);
    }

    /**
     * The regions of one window, most visits first: [country, region, visits]. No floor: a region is an area like a
     * country, no place.
     *
     * @param array<string, int> $counts 'country|region' => visits
     */
    private static function regions(array $counts, int $k): array
    {
        $regions = [];
        foreach (TopList::top(self::scale($counts, $k), self::TOP_REGIONS) as [$key, $count]) {
            [$country, $region] = explode('|', (string) $key, 2);
            $regions[] = [$country, $region, $count];
        }
        return $regions;
    }

    private static function inc(array &$map, int|string $key): void
    {
        $map[$key] = ($map[$key] ?? 0) + 1;
    }

    private static function scale(array $map, int $k): array
    {
        return $k === 1 ? $map : array_map(static fn (int $n): int => $n * $k, $map);
    }
}
