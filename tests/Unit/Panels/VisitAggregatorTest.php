<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Panels;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Panels\VisitAggregator;

/**
 * "Now" at 12:00:00; active since 11:57:00; windows of 3, 10, 30 and 60 minutes.
 *
 * @group FeinwerkLiveDashboard
 */
class VisitAggregatorTest extends TestCase
{
    private const ACTIVE_FROM = '2026-11-18 11:57:00';
    private const WINDOWS = [
        '3' => '2026-11-18 11:57:00',
        '10' => '2026-11-18 11:50:00',
        '30' => '2026-11-18 11:30:00',
        '60' => '2026-11-18 11:00:00',
    ];

    private static function row(string $time, array $overrides = []): array
    {
        return array_merge([
            'idvisit' => 1,
            'visit_last_action_time' => $time,
            'visit_exit_idaction_url' => 10,
            'visit_exit_idaction_name' => 20,
            'referer_type' => 1,
            'referer_name' => '',
            'location_country' => 'de',
            'config_device_type' => 0,
            'visit_goal_converted' => 0,
            'location_region' => 'BE',
            'location_city' => 'Berlin',
            'lat' => '52.52',
            'lon' => '13.40',
        ], $overrides);
    }

    private static function aggregate(array $rows, int $k = 1, bool $cities = true, int $cityMin = 1, array $windows = self::WINDOWS): array
    {
        $aggregator = new VisitAggregator(self::ACTIVE_FROM, $windows, $k, $cities, $cityMin);
        foreach ($rows as $row) {
            $aggregator->add($row);
        }
        return $aggregator->result();
    }

    public function test_noRows_givesZeros(): void
    {
        $r = self::aggregate([]);

        $this->assertSame(0, $r['visits']);
        $this->assertSame(0, $r['activeNow']);
        foreach (['pagesNowUrls', 'pagesNowTitles', 'channelsNow', 'countriesNow'] as $list) {
            $this->assertSame([], $r[$list], $list);
        }
        $this->assertSame(['3', '10', '30', '60'], array_map('strval', array_keys($r['windows'])));
        foreach ($r['windows'] as $window) {
            $this->assertSame(
                ['visits' => 0, 'convertedVisits' => 0, 'channels' => [], 'campaigns' => [], 'countries' => [], 'devices' => [], 'cities' => [], 'regions' => []],
                $window
            );
        }
    }

    public function test_windows_countEveryVisitInEachWindowThatHoldsIt(): void
    {
        $r = self::aggregate([
            self::row('2026-11-18 11:59:00', ['location_country' => 'at']),
            self::row('2026-11-18 11:57:00'),
            self::row('2026-11-18 11:56:59', ['referer_type' => 2]),
            self::row('2026-11-18 11:40:00', ['visit_goal_converted' => 1]),
            self::row('2026-11-18 11:29:59', ['config_device_type' => 1]),
            self::row('2026-11-18 10:59:59'), // read window wider than the widest list window: total only
        ]);

        $this->assertSame(6, $r['visits']);
        $this->assertSame(2, $r['windows']['3']['visits']);
        $this->assertSame(3, $r['windows']['10']['visits']);
        $this->assertSame(4, $r['windows']['30']['visits']);
        $this->assertSame(5, $r['windows']['60']['visits']);
        $this->assertSame(0, $r['windows']['10']['convertedVisits']);
        $this->assertSame(1, $r['windows']['30']['convertedVisits']);
        $this->assertSame([['de', 1], ['at', 1]], self::sortedByKeyDesc($r['windows']['3']['countries']));
        $this->assertSame([[1, 2], [2, 1]], $r['windows']['10']['channels']);
        $this->assertSame([[0, 4], [1, 1]], $r['windows']['60']['devices']);
    }

    public function test_activeNow_countsOnlyVisitsSinceActiveFrom(): void
    {
        $r = self::aggregate([
            self::row('2026-11-18 11:59:00', ['visit_exit_idaction_url' => 11]),
            self::row('2026-11-18 11:57:00', ['visit_exit_idaction_url' => 11]),
            self::row('2026-11-18 11:56:59', ['visit_exit_idaction_url' => 12]),
        ]);

        $this->assertSame(2, $r['activeNow']);
        $this->assertSame([[11, 2]], $r['pagesNowUrls']);
        $this->assertSame([[20, 2]], $r['pagesNowTitles']);
        $this->assertSame([[1, 2]], $r['channelsNow']);
        $this->assertSame([['de', 2]], $r['countriesNow']);
    }

    public function test_campaignSources_countWhenTheColumnIsRead(): void
    {
        $aggregator = new VisitAggregator(self::ACTIVE_FROM, self::WINDOWS, 1, false, 1, null, 0, true);
        foreach ([
            ['referer_type' => 6, 'referer_name' => 'black-friday', 'campaign_source' => 'newsletter'],
            ['referer_type' => 6, 'referer_name' => 'spring', 'campaign_source' => 'newsletter'],
            ['referer_type' => 6, 'referer_name' => 'brand', 'campaign_source' => null], // no source given
            ['referer_type' => 2, 'referer_name' => 'Google', 'campaign_source' => 'ignored'],
        ] as $columns) {
            $aggregator->add(self::row('2026-11-18 11:59:00', $columns));
        }
        $r = $aggregator->result();

        $this->assertSame([['newsletter', 2]], $r['windows']['3']['campaignSources']);
        $this->assertArrayNotHasKey('campaignSources', self::aggregate([self::row('2026-11-18 11:59:00')])['windows']['3']);
    }

    public function test_campaigns_countOnlyCampaignVisits(): void
    {
        $r = self::aggregate([
            self::row('2026-11-18 11:59:00', ['referer_type' => 6, 'referer_name' => 'black-friday']),
            self::row('2026-11-18 11:50:00', ['referer_type' => 6, 'referer_name' => 'black-friday']),
            self::row('2026-11-18 11:40:00', ['referer_type' => 2, 'referer_name' => 'Google']),
        ]);

        $this->assertSame([['black-friday', 1]], $r['windows']['3']['campaigns']);
        $this->assertSame([['black-friday', 2]], $r['windows']['30']['campaigns']);
    }

    public function test_sampledRows_areScaledByK(): void
    {
        $r = self::aggregate([self::row('2026-11-18 11:59:00'), self::row('2026-11-18 11:50:00')], 3);

        $this->assertSame(6, $r['visits']);
        $this->assertSame(3, $r['activeNow']);
        $this->assertSame(3, $r['windows']['3']['visits']);
        $this->assertSame([['de', 6]], $r['windows']['10']['countries']);
        $this->assertSame([[10, 3]], $r['pagesNowUrls']);
    }

    public function test_nullColumns_countAsUnknown(): void
    {
        $r = self::aggregate([
            self::row('2026-11-18 11:59:00', [
                'location_country' => null, 'referer_type' => null, 'referer_name' => null, 'config_device_type' => null,
                'visit_exit_idaction_url' => null, 'visit_exit_idaction_name' => null, 'lat' => null, 'lon' => null,
            ]),
            self::row('2026-11-18 11:58:00', ['location_country' => '', 'referer_type' => 6, 'referer_name' => null]),
            self::row('2026-11-18 11:58:00', ['referer_type' => 6, 'referer_name' => '<b>Sale</b> & "more"']),
        ]);
        $w = $r['windows']['3'];

        $this->assertSame([['de', 1], ['xx', 2]], self::sorted($w['countries']));
        $this->assertSame([[0, 1], [6, 2]], self::sorted($w['channels']));
        $this->assertSame([['', 1], ['<b>Sale</b> & "more"', 1]], self::sorted($w['campaigns']));
        $this->assertSame([[-1, 1], [0, 2]], self::sorted($w['devices']));
        $this->assertSame([[0, 1], [10, 2]], self::sorted($r['pagesNowUrls']));
        // the first visit has no coordinates, the other two differ in country
        $this->assertSame([['de', 'BE', 'Berlin', 52.52, 13.4, 1], ['xx', 'BE', 'Berlin', 52.52, 13.4, 1]], $w['cities']);
    }

    public function test_cityBelowThreshold_isHidden(): void
    {
        $paris = ['location_country' => 'fr', 'location_region' => 'IDF', 'location_city' => 'Paris', 'lat' => '48.86', 'lon' => '2.35'];
        $r = self::aggregate([
            self::row('2026-11-18 11:59:00'),
            self::row('2026-11-18 11:58:00'),
            self::row('2026-11-18 11:58:00', $paris),
            self::row('2026-11-18 11:57:00', $paris),
            self::row('2026-11-18 11:56:00', $paris),
        ], 1, true, 3);

        $this->assertSame([['fr', 'IDF', 'Paris', 48.86, 2.35, 3]], $r['windows']['10']['cities']);
        $this->assertSame([], $r['windows']['3']['cities'], 'the threshold holds per window: 2 visits in 3 minutes');
    }

    public function test_cityFloor_countsTheVisitsSeen_notTheEstimate(): void
    {
        // sampled with k = 3: one visit seen stands for 3, but a single visit must never show its town
        $town = ['location_country' => 'de', 'location_region' => 'BY', 'location_city' => 'Kleinstadt', 'lat' => '48.10', 'lon' => '11.10'];
        $one = self::aggregate([self::row('2026-11-18 11:59:00', $town)], 3, true, 3);
        $this->assertSame([], $one['windows']['3']['cities']);

        $three = self::aggregate(array_fill(0, 3, self::row('2026-11-18 11:59:00', $town)), 3, true, 3);
        $this->assertSame([['de', 'BY', 'Kleinstadt', 48.1, 11.1, 9]], $three['windows']['3']['cities'], 'shown with the estimate');
    }

    public function test_cities_followTheListWindows(): void
    {
        $berlin = ['location_country' => 'de', 'location_region' => 'BE', 'location_city' => 'Berlin', 'lat' => '52.52', 'lon' => '13.40'];
        $r = self::aggregate([
            self::row('2026-11-18 11:59:00', $berlin),
            self::row('2026-11-18 11:52:00', $berlin),
            self::row('2026-11-18 11:20:00', $berlin),
        ]);

        $this->assertSame([['de', 'BE', 'Berlin', 52.52, 13.4, 1]], $r['windows']['3']['cities']);
        $this->assertSame([['de', 'BE', 'Berlin', 52.52, 13.4, 2]], $r['windows']['10']['cities']);
        $this->assertSame([['de', 'BE', 'Berlin', 52.52, 13.4, 3]], $r['windows']['60']['cities']);
        $this->assertArrayNotHasKey('cities', $r, 'no list outside the windows');
    }

    public function test_regions_countEveryVisitWithARegion_perWindow(): void
    {
        $r = self::aggregate([
            self::row('2026-11-18 11:59:00', ['location_country' => 'de', 'location_region' => 'BY', 'lat' => '48.14', 'lon' => '11.58']),
            self::row('2026-11-18 11:58:00', ['location_country' => 'de', 'location_region' => 'BY']), // no coordinates
            self::row('2026-11-18 11:52:00', ['location_country' => 'de', 'location_region' => 'BE']),
            self::row('2026-11-18 11:52:00', ['location_country' => 'de', 'location_region' => '']), // no region
        ], 1, true, 3);

        $this->assertSame([['de', 'BY', 2]], $r['windows']['3']['regions']);
        $this->assertSame([['de', 'BY', 2], ['de', 'BE', 1]], $r['windows']['10']['regions'], 'no floor: a region is no place');
        $this->assertSame([], self::aggregate([self::row('2026-11-18 11:59:00', ['location_region' => 'BY'])], 1, false)['windows']['3']['regions']);
    }

    public function test_withoutCities_noCityList(): void
    {
        $this->assertSame([], self::aggregate([self::row('2026-11-18 11:59:00')], 1, false)['windows']['3']['cities']);
    }

    public function test_visitStarts_perMinuteOldestFirst(): void
    {
        $aggregator = new VisitAggregator(self::ACTIVE_FROM, self::WINDOWS, 2, false, 1, strtotime('2026-11-18 12:00:00 UTC'), 60);
        foreach ([
            ['2026-11-18 11:59:30', '2026-11-18 11:59:50'],
            ['2026-11-18 11:59:00', '2026-11-18 11:59:10'],
            ['2026-11-18 11:20:00', '2026-11-18 11:58:00'], // a long visit: counted in the minute it started
            ['2026-11-18 10:59:59', '2026-11-18 11:30:00'], // started before the sparkline
        ] as [$first, $last]) {
            $aggregator->add(self::row($last, ['visit_first_action_time' => $first]));
        }
        $spark = $aggregator->result()['visitSparkline'];

        $this->assertCount(60, $spark);
        $this->assertSame(4, $spark[58], '11:59, two visits times k = 2');
        $this->assertSame(2, $spark[19], '11:20');
        $this->assertSame(0, $spark[59]);
        $this->assertSame(6, array_sum($spark));
    }

    public function test_withoutSparkline_noVisitSparkline(): void
    {
        $this->assertSame([], self::aggregate([self::row('2026-11-18 11:59:00')])['visitSparkline']);
    }

    public function test_oneWindow_forTheFastTier(): void
    {
        $r = self::aggregate([self::row('2026-11-18 11:59:00'), self::row('2026-11-18 11:54:00')], 1, false, 1, ['5' => '2026-11-18 11:55:00']);

        $this->assertSame(1, $r['windows']['5']['visits']);
        $this->assertSame(2, $r['visits']);
    }

    private static function sorted(array $list): array
    {
        usort($list, static fn (array $a, array $b): int => strcmp((string) $a[0], (string) $b[0]));
        return $list;
    }

    private static function sortedByKeyDesc(array $list): array
    {
        usort($list, static fn (array $a, array $b): int => strcmp((string) $b[0], (string) $a[0]));
        return $list;
    }
}
