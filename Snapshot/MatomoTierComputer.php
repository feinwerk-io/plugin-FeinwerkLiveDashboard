<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Snapshot;

use Piwik\Piwik;
use Piwik\Plugins\FeinwerkLiveDashboard\Data\LogQueries;
use Piwik\Plugins\FeinwerkLiveDashboard\Format\LiveLogIcons;
use Piwik\Plugins\FeinwerkLiveDashboard\Data\LogReader;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\LocalDay;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Sampling;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Schedule;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Windows;
use Piwik\Plugins\FeinwerkLiveDashboard\Panels\ActionAggregator;
use Piwik\Plugins\FeinwerkLiveDashboard\Panels\ActionKinds;
use Piwik\Plugins\FeinwerkLiveDashboard\Panels\ConversionAggregator;
use Piwik\Plugins\FeinwerkLiveDashboard\Panels\TopList;
use Piwik\Plugins\FeinwerkLiveDashboard\Panels\VisitAggregator;

/**
 * Computes the tiers from the log tables (concept 3.4): per tier one bounded index range read per log table, aggregated
 * in one PHP pass, plus batched log_action lookups for the top ids only (all ids for the type of a hit, up to a cap).
 * Before each read after the first, the budget is checked; when it ran out, the result is partial and the missing keys
 * keep their previous values.
 */
final class MatomoTierComputer implements TierComputer
{
    public const TOP = 50;
    public const LIVE_LOG_ROWS = 10;
    private const LOOKUP_CHUNK = 1000;

    /** @var callable fn(): bool, MarketingCampaignsReporting is active (its column log_visit.campaign_source exists) */
    private $campaignSourceAvailable;

    public function __construct(private readonly LogReader $reader, private readonly LogQueries $queries, ?callable $campaignSourceAvailable = null)
    {
        $this->campaignSourceAvailable = $campaignSourceAvailable ?? static function (): bool {
            try {
                return \Piwik\Plugin\Manager::getInstance()->isPluginActivated('MarketingCampaignsReporting');
            } catch (\Throwable $e) {
                return false;
            }
        };
    }

    public function compute(Tier $tier, ComputeContext $ctx): TierResult
    {
        return match ($tier) {
            Tier::Fast => $this->fast($ctx),
            Tier::Slow => $this->slow($ctx),
            Tier::Today => $this->today($ctx),
        };
    }

    private function fast(ComputeContext $ctx): TierResult
    {
        $lag = $this->reader->lagSeconds($ctx->idSite);
        $w = Windows::compute($ctx->now, $lag, $ctx->limits);
        $end = Windows::utc($w->end);
        $payload = ['lag' => $lag];

        if ($ctx->level >= 3) {
            // counters only: index ranges without row lookups
            $payload['activeNow'] = $this->count($this->queries->countVisits($ctx->idSite, Windows::utc($w->activeFrom), $end));
            $payload['visits5'] = $this->count($this->queries->countVisits($ctx->idSite, Windows::utc($w->fiveFrom), $end));
            $payload['actions5'] = $this->count($this->queries->countActions($ctx->idSite, Windows::utc($w->fiveFrom), $end));
            return new TierResult($payload, $w->end, false);
        }

        $count = $this->count($this->queries->countVisits($ctx->idSite, Windows::utc($w->fastFrom), $end));
        $k = Sampling::factor($count, Schedule::effectiveCap($ctx->limits->visitRowCap, $ctx->level));
        $visits = new VisitAggregator(Windows::utc($w->activeFrom), ['5' => Windows::utc($w->fiveFrom)], $k, false, $ctx->limits->cityMinVisits);
        $this->reader->each($this->queries->visitRows($ctx->idSite, Windows::utc($w->fastFrom), $end, $k, false), [$visits, 'add']);
        $v = $visits->result();
        // the live log (a visitor-level panel): only while the website's visits log is on (concept 3.10)
        $newest = $ctx->visitorLogEnabled
            ? $this->reader->rows($this->queries->newestVisits($ctx->idSite, Windows::utc($w->fastFrom), $end, self::LIVE_LOG_ROWS))
            : [];
        $names = $this->names(array_merge(
            array_column($v['pagesNowUrls'], 0),
            array_column($v['pagesNowTitles'], 0),
            array_column($newest, 'visit_exit_idaction_url'),
            array_column($newest, 'visit_exit_idaction_name')
        ));
        if ($ctx->visitorLogEnabled) {
            $flags = [];
            foreach ($newest as $r) {
                $flags[(int) $r['idvisit']] = LiveLogIcons::flags($r);
            }
            if ($flags !== []) {
                // other plugins add their places (LiveLogIcons); the idvisit itself never goes into the payload
                try {
                    Piwik::postEvent(LiveLogIcons::EVENT_FLAGS, [&$flags, $ctx->idSite, array_keys($flags)]);
                } catch (\Throwable $e) {
                    // a failing extension must not take the live log with it
                }
            }
            $payload['newestVisits'] = array_map(static fn (array $r): array => [
                (int) strtotime($r['visit_last_action_time'] . ' UTC'),
                (string) ($r['location_country'] ?? '') === '' ? VisitAggregator::UNKNOWN_COUNTRY : (string) $r['location_country'],
                (int) ($r['referer_type'] ?? 0),
                (string) ($r['referer_name'] ?? ''),
                isset($r['config_device_type']) ? (int) $r['config_device_type'] : VisitAggregator::UNKNOWN_DEVICE,
                (int) ($r['visit_exit_idaction_url'] ?? 0),
                $names[(int) ($r['visit_exit_idaction_url'] ?? 0)] ?? '',
                $names[(int) ($r['visit_exit_idaction_name'] ?? 0)] ?? '',
                (int) ($r['visit_total_actions'] ?? 0),
                array_values(array_unique(array_map('strval', (array) ($flags[(int) $r['idvisit']] ?? [])))),
                // how long the visit has lasted up to its last action (seconds)
                max(0, (int) strtotime($r['visit_last_action_time'] . ' UTC') - (int) strtotime(($r['visit_first_action_time'] ?? $r['visit_last_action_time']) . ' UTC')),
            ], $newest);
        }
        $payload += [
            'activeNow' => $v['activeNow'],
            'visits5' => $v['windows']['5']['visits'],
            'pagesNow' => ['urls' => self::named($v['pagesNowUrls'], $names), 'titles' => self::named($v['pagesNowTitles'], $names)],
            'channelsNow' => $v['channelsNow'],
            'countriesNow' => $v['countriesNow'],
            'k' => $k,
        ];
        $capHit = $k > 1;

        if ($ctx->budget->exhausted()) {
            return new TierResult($payload, $w->end, $capHit, true);
        }
        $payload['actions5'] = $this->count($this->queries->countActions($ctx->idSite, Windows::utc($w->fiveFrom), $end));

        if ($ctx->budget->exhausted()) {
            return new TierResult($payload, $w->end, $capHit, true);
        }
        $totals = $this->reader->rows($this->queries->conversionTotals($ctx->idSite, Windows::utc($w->fiveFrom), $end))[0] ?? [];
        $payload['conversions5'] = (int) ($totals['conversions'] ?? 0);
        $payload['revenue5'] = round((float) ($totals['revenue'] ?? 0), 2);

        return new TierResult($payload, $w->end, $capHit);
    }

    /**
     * The lists and key numbers of every list window (3, 10, 30, 60 minutes) from one read of the widest window. Each
     * value of the payload is a map window => value, so a partial result (budget ended) keeps whole keys.
     */
    private function slow(ComputeContext $ctx): TierResult
    {
        $lag = $this->reader->lagSeconds($ctx->idSite);
        $w = Windows::compute($ctx->now, $lag, $ctx->limits);
        $from = Windows::utc($w->slowFrom);
        $end = Windows::utc($w->end);
        $froms = $w->listFroms();
        $level = $ctx->level;

        $count = $this->count($this->queries->countVisits($ctx->idSite, $from, $end));
        $k = Sampling::factor($count, Schedule::effectiveCap($ctx->limits->visitRowCap, $level));
        $cities = $level < 2;
        $sources = (bool) ($this->campaignSourceAvailable)();
        $visits = new VisitAggregator(
            Windows::utc($w->activeFrom), $froms, $k, $cities, $ctx->limits->cityMinVisits, $w->end, max(Windows::LIST_MINUTES), $sources
        );
        $this->reader->each($this->queries->visitRows($ctx->idSite, $from, $end, $k, $cities, $sources), [$visits, 'add']);
        $v = $visits->result();
        $keys = ['visits', 'convertedVisits', 'channels', 'campaigns', 'countries', 'devices', 'cities', 'regions'];
        $payload = self::pivot($v['windows'], $sources ? array_merge($keys, ['campaignSources']) : $keys);
        // the end of the minutes counted: the bars' time labels start from it (earlier while the reader is behind)
        $payload += ['visitSparkline' => $v['visitSparkline'], 'kVisits' => $k, 'end' => $w->end];
        $capHit = $k > 1;

        if ($ctx->budget->exhausted()) {
            return new TierResult($payload, $w->end, $capHit, true);
        }
        $actionCount = $this->count($this->queries->countActions($ctx->idSite, $from, $end));
        $ka = Sampling::factor($actionCount, Schedule::effectiveCap($ctx->limits->actionRowCap, $level));
        $capHit = $capHit || $ka > 1;
        $actions = new ActionAggregator($froms, $w->end, $ka, max(Windows::LIST_MINUTES));
        $this->reader->each($this->queries->actionRows($ctx->idSite, $from, $end, $ka), [$actions, 'add']);
        $payload += $this->actionPanels($actions->result(), $ctx);
        $payload['kActions'] = $ka;

        if ($ctx->budget->exhausted()) {
            return new TierResult($payload, $w->end, $capHit, true);
        }
        $conversions = new ConversionAggregator($froms);
        $this->reader->each($this->queries->conversionGroups($ctx->idSite, $from, $end, $froms), [$conversions, 'addGroup']);
        $payload += self::pivot($conversions->result(), ['conversions', 'revenue', 'orders', 'orderRevenue', 'goals']);

        return new TierResult($payload, $w->end, $capHit);
    }

    /**
     * window => [key => value] as key => [window => value]
     *
     * @param array<string, array> $windows
     * @param string[] $keys
     */
    private static function pivot(array $windows, array $keys): array
    {
        $out = array_fill_keys($keys, []);
        foreach ($windows as $window => $values) {
            foreach ($keys as $key) {
                $out[$key][(string) $window] = $values[$key];
            }
        }
        return $out;
    }

    private function today(ComputeContext $ctx): TierResult
    {
        $since = LocalDay::startUtc($ctx->now, $ctx->timezone);
        $payload = [
            'since' => $since,
            // visits move their last action time forward: counted from midnight every time
            'visits' => $this->count($this->queries->countVisits($ctx->idSite, Windows::utc($since), Windows::utc($ctx->now))),
        ];
        if ($ctx->budget->exhausted()) {
            return new TierResult($payload, $ctx->now, false, true);
        }

        // hits and conversions: the settled part of the day from the last run plus the minutes since (IncrementalCount);
        // the counting state of a measure changes only together with its value. IncrementalCount reads [a, b), the
        // database ranges include their last second (DATETIME): so up to b - 1
        $lag = $this->reader->lagSeconds($ctx->idSite);
        $counted = is_array($ctx->previous['counted'] ?? null) ? $ctx->previous['counted'] : [];
        $state = static fn (string $key): ?array => is_array($counted[$key] ?? null) ? $counted[$key] : null;

        [$actions, $counted['actions']] = IncrementalCount::run(
            fn (int $a, int $b): array => ['n' => $this->count($this->queries->countActions($ctx->idSite, Windows::utc($a), Windows::utc($b - 1)))],
            ['n' => 0],
            $state('actions'),
            $since,
            $ctx->now,
            $lag
        );
        $payload['actions'] = $actions['n'];
        $payload['counted'] = $counted;
        if ($ctx->budget->exhausted()) {
            return new TierResult($payload, $ctx->now, false, true);
        }

        [$conversions, $counted['conversions']] = IncrementalCount::run(
            function (int $a, int $b) use ($ctx): array {
                $totals = $this->reader->rows($this->queries->conversionTotals($ctx->idSite, Windows::utc($a), Windows::utc($b - 1)))[0] ?? [];
                return ['conversions' => (int) ($totals['conversions'] ?? 0), 'revenue' => (float) ($totals['revenue'] ?? 0)];
            },
            ['conversions' => 0, 'revenue' => 0.0],
            $state('conversions'),
            $since,
            $ctx->now,
            $lag
        );
        $payload['conversions'] = $conversions['conversions'];
        $payload['revenue'] = round($conversions['revenue'], 2);
        $payload['counted'] = $counted;
        return new TierResult($payload, $ctx->now, false);
    }

    /**
     * Splits the hit counts of every window into pages, downloads, outlinks and site search by log_action.type, and names
     * the top ids. The widest window holds every id, so its maps decide which types are looked up.
     */
    private function actionPanels(array $a, ComputeContext $ctx): array
    {
        $widest = end($a['windows']) ?: [];
        $lookupIds = array_merge(
            array_column(TopList::top($widest['urls'] ?? [], $ctx->limits->typeLookupCap), 0),
            array_column(TopList::top($widest['searchNames'] ?? [], self::TOP * 4), 0)
        );
        $types = $this->types($lookupIds, $ctx);

        $lists = [];
        $counts = ['actions' => [], 'pageviews' => []];
        $typesPartial = false;
        foreach ($a['windows'] as $window => $maps) {
            $window = (string) $window;
            $split = ActionKinds::split($maps['urls'], $types);
            $typesPartial = $typesPartial || $split['typesPartial'];
            $counts['actions'][$window] = $maps['actions'];
            $counts['pageviews'][$window] = array_sum($split['pages']);
            $lists['topPages'][$window] = TopList::top($split['pages'], self::TOP);
            $lists['topTitles'][$window] = TopList::top($maps['titles'], self::TOP);
            $lists['downloads'][$window] = TopList::top($split['downloads'], self::TOP);
            $lists['outlinks'][$window] = TopList::top($split['outlinks'], self::TOP);
            $lists['siteSearch'][$window] = TopList::top(ActionKinds::searches($maps['searchNames'], $types), self::TOP);
            $lists['eventCategories'][$window] = TopList::top($maps['eventCategories'], self::TOP);
            $lists['eventActions'][$window] = TopList::top($maps['eventActions'], self::TOP);
        }

        $ids = [];
        foreach ($lists as $byWindow) {
            foreach ($byWindow as $list) {
                $ids = array_merge($ids, array_column($list, 0));
            }
        }
        $names = $this->names($ids);
        foreach ($lists as $key => $byWindow) {
            foreach ($byWindow as $window => $list) {
                $lists[$key][$window] = self::named($list, $names);
            }
        }

        return $counts + $lists + ['sparkline' => $a['sparkline'], 'typesPartial' => $typesPartial];
    }

    /** @return array<int, int> idaction => type; stops early when the budget runs out (ids then count as pages) */
    private function types(array $ids, ComputeContext $ctx): array
    {
        $types = [];
        foreach (array_chunk(array_values(array_unique($ids)), self::LOOKUP_CHUNK) as $chunk) {
            if ($types !== [] && $ctx->budget->exhausted()) {
                break;
            }
            $query = $this->queries->actionTypes($chunk);
            if ($query === null) {
                continue;
            }
            foreach ($this->reader->rows($query) as $row) {
                $types[(int) $row['idaction']] = (int) $row['type'];
            }
        }
        return $types;
    }

    /** @return array<int, string> idaction => name */
    private function names(array $ids): array
    {
        $names = [];
        foreach (array_chunk(array_values(array_unique($ids)), self::LOOKUP_CHUNK) as $chunk) {
            $query = $this->queries->actionNames($chunk);
            if ($query === null) {
                continue;
            }
            foreach ($this->reader->rows($query) as $row) {
                $names[(int) $row['idaction']] = (string) $row['name'];
            }
        }
        return $names;
    }

    /**
     * @param array<int, array{0: int, 1: int}> $list [id, count]
     * @return array<int, array{0: int, 1: string, 2: int}> [id, name, count]; unknown ids (0, deleted) get ''
     */
    private static function named(array $list, array $names): array
    {
        return array_map(static fn (array $row): array => [(int) $row[0], $names[(int) $row[0]] ?? '', $row[1]], $list);
    }

    private function count(array $query): int
    {
        return (int) ($this->reader->scalar($query) ?? 0);
    }
}
