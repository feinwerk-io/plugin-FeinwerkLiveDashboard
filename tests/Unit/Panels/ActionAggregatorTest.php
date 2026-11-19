<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Panels;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Panels\ActionAggregator;
use Piwik\Plugins\FeinwerkLiveDashboard\Panels\ActionKinds;

/**
 * "Now" at 12:00:30; windows of 3, 10, 30 and 60 minutes; a sparkline of 60 minutes.
 *
 * @group FeinwerkLiveDashboard
 */
class ActionAggregatorTest extends TestCase
{
    private const WINDOWS = [
        '3' => '2026-11-18 11:57:30',
        '10' => '2026-11-18 11:50:30',
        '30' => '2026-11-18 11:30:30',
        '60' => '2026-11-18 11:00:30',
    ];

    private static function end(): int
    {
        return strtotime('2026-11-18 12:00:30 UTC');
    }

    private static function pageview(string $time, int $url = 100, ?int $name = 200): array
    {
        return ['idlink_va' => 1, 'server_time' => $time, 'idaction_url' => $url, 'idaction_name' => $name,
            'idaction_event_category' => null, 'idaction_event_action' => null];
    }

    private static function aggregate(array $rows, int $k = 1): array
    {
        $aggregator = new ActionAggregator(self::WINDOWS, self::end(), $k, 60);
        foreach ($rows as $row) {
            $aggregator->add($row);
        }
        return $aggregator->result();
    }

    public function test_noRows_givesZeros(): void
    {
        $r = self::aggregate([]);
        $this->assertSame(0, $r['actions']);
        $this->assertSame(array_fill(0, 60, 0), $r['sparkline']);
        foreach ($r['windows'] as $window) {
            $this->assertSame(
                ['actions' => 0, 'urls' => [], 'titles' => [], 'searchNames' => [], 'eventCategories' => [], 'eventActions' => []],
                $window
            );
        }
        $this->assertSame(['3', '10', '30', '60'], array_map('strval', array_keys($r['windows'])));
    }

    public function test_pageRows_countUrlsAndTitlesPerWindow(): void
    {
        $r = self::aggregate([
            self::pageview('2026-11-18 12:00:10', 100, 200),
            self::pageview('2026-11-18 11:58:00', 100, 200),
            self::pageview('2026-11-18 11:40:00', 101, 201),
            self::pageview('2026-11-18 11:20:00', 102, null), // a download: no title
        ]);

        $this->assertSame(4, $r['actions']);
        $this->assertSame(2, $r['windows']['3']['actions']);
        $this->assertSame([100 => 2], $r['windows']['3']['urls']);
        $this->assertSame([100 => 2], $r['windows']['10']['urls']);
        $this->assertSame([100 => 2, 101 => 1], $r['windows']['30']['urls']);
        $this->assertSame([100 => 2, 101 => 1, 102 => 1], $r['windows']['60']['urls']);
        $this->assertSame([200 => 2, 201 => 1], $r['windows']['60']['titles']);
    }

    public function test_events_countCategoryAndActionButNoPage(): void
    {
        $event = ['idlink_va' => 2, 'server_time' => '2026-11-18 11:59:00', 'idaction_url' => 100, 'idaction_name' => 300,
            'idaction_event_category' => 400, 'idaction_event_action' => 401];

        $r = self::aggregate([$event, $event]);

        $this->assertSame([400 => 2], $r['windows']['3']['eventCategories']);
        $this->assertSame([401 => 2], $r['windows']['60']['eventActions']);
        $this->assertSame([], $r['windows']['60']['urls']);
        $this->assertSame(2, $r['actions']);
    }

    public function test_siteSearch_hasNoUrlButAKeyword(): void
    {
        $r = self::aggregate([self::pageview('2026-11-18 11:59:00', 0, 500), ['idlink_va' => 3, 'server_time' => '2026-11-18 11:59:00',
            'idaction_url' => null, 'idaction_name' => 500, 'idaction_event_category' => null, 'idaction_event_action' => null]]);

        $this->assertSame([500 => 2], $r['windows']['3']['searchNames']);
        $this->assertSame([], $r['windows']['3']['urls']);
    }

    public function test_sparkline_hasSixtyMinutesOldestFirst(): void
    {
        $r = self::aggregate([
            self::pageview('2026-11-18 12:00:10'),
            self::pageview('2026-11-18 11:59:59'),
            self::pageview('2026-11-18 11:59:00'),
            self::pageview('2026-11-18 11:01:00'),
            self::pageview('2026-11-18 11:00:59'), // before the first full minute of the sparkline
        ]);

        $this->assertCount(60, $r['sparkline']);
        $this->assertSame(1, $r['sparkline'][59]);
        $this->assertSame(2, $r['sparkline'][58]);
        $this->assertSame(1, $r['sparkline'][0]);
        $this->assertSame(4, array_sum($r['sparkline']));
    }

    public function test_sampledRows_areScaledByK(): void
    {
        $r = self::aggregate([self::pageview('2026-11-18 11:59:00')], 4);
        $this->assertSame(4, $r['actions']);
        $this->assertSame([100 => 4], $r['windows']['3']['urls']);
        $this->assertSame(4, $r['windows']['60']['actions']);
        $this->assertSame(4, $r['sparkline'][58]);
    }

    public function test_split_movesDownloadsAndOutlinks(): void
    {
        $split = ActionKinds::split([1 => 5, 2 => 4, 3 => 3, 4 => 2], [1 => 1, 2 => 3, 3 => 2, 4 => 13]);

        $this->assertSame([1 => 5], $split['pages']);
        $this->assertSame([2 => 4], $split['downloads']);
        $this->assertSame([3 => 3], $split['outlinks']);
        $this->assertFalse($split['typesPartial']);
    }

    public function test_split_countsUnknownTypesAsPagesAndSaysSo(): void
    {
        $split = ActionKinds::split([1 => 5, 9 => 1], [1 => 1]);

        $this->assertSame([1 => 5, 9 => 1], $split['pages']);
        $this->assertTrue($split['typesPartial']);
    }

    public function test_searches_keepOnlySiteSearchKeywords(): void
    {
        $this->assertSame([5 => 2], ActionKinds::searches([5 => 2, 6 => 1, 7 => 1], [5 => 8, 6 => 4]));
    }
}
