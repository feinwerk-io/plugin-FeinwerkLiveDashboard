<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Integration;

use Piwik\Common;
use Piwik\Plugins\FeinwerkLiveDashboard\Data\DbLogReader;
use Piwik\Plugins\FeinwerkLiveDashboard\Data\LogQueries;
use Piwik\Plugins\FeinwerkLiveDashboard\Data\QueryTimeout;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Budget;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Limits;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\ComputeContext;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\IncrementalCount;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\MatomoTierComputer;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\TierResult;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes\FixedClock;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fixtures\LiveTrafficFixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Every number of the payload against a hand count of LiveTrafficFixture (see its table).
 *
 * @group FeinwerkLiveDashboard
 */
class TierComputerTest extends IntegrationTestCase
{
    /** @var LiveTrafficFixture */
    public static $fixture = null;

    private static function now(): int
    {
        return strtotime(LiveTrafficFixture::NOW . ' UTC');
    }

    private static function compute(Tier $tier, ?Limits $limits = null, int $level = 0, int $idSite = LiveTrafficFixture::SITE, float $spent = 0.0, bool $visitorLog = true, array $previous = [], ?int $now = null): TierResult
    {
        $limits = $limits ?? new Limits();
        $now = $now ?? self::now();
        $clock = new FixedClock($now);
        $budget = new Budget($clock, $limits->budgetSeconds);
        $clock->advance($spent);
        $computer = new MatomoTierComputer(new DbLogReader($limits), new LogQueries([Common::class, 'prefixTable']));
        $ctx = new ComputeContext($idSite, 0, $now, 'Europe/Berlin', $level, $limits, $budget, $visitorLog, $previous);
        return $computer->compute($tier, $ctx);
    }

    /** [id, name, count] rows as name => count */
    private static function byName(array $rows): array
    {
        $map = [];
        foreach ($rows as [$id, $name, $count]) {
            self::assertIsInt($id);
            $map[$name] = $count;
        }
        return $map;
    }

    public function test_fast_countsActiveVisitsAndPagesViewedNow(): void
    {
        $r = self::compute(Tier::Fast);
        $p = $r->payload;

        $this->assertSame(0, $p['lag']);
        $this->assertSame(2, $p['activeNow']);
        $this->assertSame(3, $p['visits5']);
        $this->assertSame(9, $p['actions5'], 'every hit: pageviews, download, content impression, event, outlink, search');
        $this->assertSame(1, $p['conversions5']);
        $this->assertSame(5.0, $p['revenue5']);
        $this->assertSame(['shop.example/home' => 1, 'shop.example/product' => 1], self::byName($p['pagesNow']['urls']));
        $this->assertSame(['Home' => 1, 'Product' => 1], self::byName($p['pagesNow']['titles']));
        $this->assertSame([[2, 1], [6, 1]], $p['channelsNow']);
        $this->assertSame([['at', 1], ['de', 1]], $p['countriesNow']);
        $this->assertSame(1, $p['k']);
        $this->assertSame(self::now(), $r->dataUntil);
        $this->assertFalse($r->capHit);
        $this->assertFalse($r->partial);
    }

    public function test_fast_listsTheNewestVisitsWithoutIdentifiers(): void
    {
        $visits = self::compute(Tier::Fast)->payload['newestVisits'];

        // V1 11:59 (campaign, 2 pageviews), V2 11:58 (search, phone), V3 11:56:50 (direct, goal, last action a search)
        $this->assertCount(3, $visits);
        $this->assertSame(
            [strtotime('2026-03-10 11:59:00 UTC'), 'de', 6, 'black-friday', 0, 'shop.example/product', 'Product', 2, []],
            [$visits[0][0], $visits[0][1], $visits[0][2], $visits[0][3], $visits[0][4], $visits[0][6], $visits[0][7], $visits[0][8], $visits[0][9]]
        );
        $this->assertSame(['at', 2, 'Google', 1], [$visits[1][1], $visits[1][2], $visits[1][3], $visits[1][4]]);
        $this->assertSame([0, ['goal']], [$visits[2][5], $visits[2][9]], 'site search as last action: no exit page; a goal');
        $this->assertCount(11, $visits[0], 'no idvisit in the payload');
        // V1: first action 11:58, last 11:59
        $this->assertSame(60, $visits[0][10], 'visit duration in seconds');
    }

    public function test_otherPlugins_addFlagsToTheVisitsShown(): void
    {
        $seen = [];
        \Piwik\Piwik::addAction('FeinwerkLiveDashboard.addLiveLogFlags', static function (array &$flags, int $idSite, array $idVisits) use (&$seen): void {
            $seen = [$idSite, count($idVisits)];
            foreach ($idVisits as $idVisit) {
                $flags[$idVisit][] = 'crash';
            }
        });

        $visits = self::compute(Tier::Fast)->payload['newestVisits'];

        $this->assertSame([LiveTrafficFixture::SITE, 3], $seen);
        $this->assertSame(['crash'], $visits[0][9]);
        $this->assertSame(['goal', 'crash'], $visits[2][9]);
    }

    public function test_campaignSources_onlyWithMarketingCampaignsReporting(): void
    {
        $limits = new Limits();
        $clock = new FixedClock(self::now());
        $ctx = new ComputeContext(LiveTrafficFixture::SITE, 0, self::now(), 'Europe/Berlin', 0, $limits, new Budget($clock, 8), true);
        $without = new MatomoTierComputer(new DbLogReader($limits), new LogQueries([Common::class, 'prefixTable']), static fn (): bool => false);
        $this->assertArrayNotHasKey('campaignSources', $without->compute(Tier::Slow, $ctx)->payload);

        if (!\Piwik\Plugin\Manager::getInstance()->isPluginActivated('MarketingCampaignsReporting')) {
            $this->markTestSkipped('MarketingCampaignsReporting is not active in this test environment');
        }
        $with = new MatomoTierComputer(new DbLogReader($limits), new LogQueries([Common::class, 'prefixTable']));
        $sources = $with->compute(Tier::Slow, $ctx)->payload['campaignSources'];
        $this->assertSame(['3', '10', '30', '60'], array_map('strval', array_keys($sources)));
    }

    public function test_fast_withoutTheVisitsLog_hasNoLiveLog(): void
    {
        $p = self::compute(Tier::Fast, null, 0, LiveTrafficFixture::SITE, 0.0, false)->payload;
        $this->assertArrayNotHasKey('newestVisits', $p);
        $this->assertSame(2, $p['activeNow']);
    }

    public function test_slow_countsEveryListWindow(): void
    {
        $r = self::compute(Tier::Slow, new Limits(cityMinVisits: 1));
        $p = $r->payload;

        // visits by last action: V1 11:59, V2 11:58 | V3 11:56:50, V4 11:50:30 | V5 11:20
        $this->assertSame(['3' => 2, '10' => 4, '30' => 4, '60' => 5], $p['visits']);
        $this->assertSame(['3' => 0, '10' => 2, '30' => 2, '60' => 2], $p['convertedVisits']);
        $this->assertSame([[2, 1], [6, 1]], $p['channels']['3']);
        $this->assertSame([[1, 2], [2, 1], [6, 1]], $p['channels']['10']);
        $this->assertSame([[1, 3], [2, 1], [6, 1]], $p['channels']['60']);
        $this->assertSame([['black-friday', 1]], $p['campaigns']['60']);
        $this->assertSame([['at', 1], ['de', 1]], $p['countries']['3']);
        $this->assertSame([['de', 3], ['at', 1], ['fr', 1]], $p['countries']['60']);
        $this->assertSame([[0, 1], [1, 1]], $p['devices']['3']);
        $this->assertSame([[0, 4], [1, 1]], $p['devices']['60']);
        $this->assertSame([['de', 'BE', 'Berlin', 52.52, 13.4, 1]], $p['cities']['3']);
        $this->assertSame([['de', 'BE', 'Berlin', 52.52, 13.4, 1]], $p['cities']['60']);
        $this->assertSame([['de', 'BE', 1]], $p['regions']['3']);
        $this->assertSame(strtotime('2026-03-10 12:00:00 UTC'), $p['end'], 'the end of the counted minutes');

        // hits by server time: 4 since 11:57, 10 since 11:50, 11 since 11:00
        $this->assertSame(['3' => 4, '10' => 10, '30' => 10, '60' => 11], $p['actions']);
        $this->assertSame(['3' => 3, '10' => 5, '30' => 5, '60' => 6], $p['pageviews']);
        $this->assertSame(['shop.example/home' => 2, 'shop.example/product' => 1], self::byName($p['topPages']['3']));
        $this->assertSame(['shop.example/home' => 4, 'shop.example/product' => 2], self::byName($p['topPages']['60']));
        $this->assertSame(['Home' => 3, 'Product' => 2], self::byName($p['topTitles']['30']));
        $this->assertSame(['http://shop.example/file.pdf' => 1], self::byName($p['downloads']['3']));
        $this->assertSame([], $p['outlinks']['3']);
        $this->assertSame(['https://partner.example/offer' => 1], self::byName($p['outlinks']['10']));
        $this->assertSame(['shoes' => 1], self::byName($p['siteSearch']['10']));
        $this->assertSame(['Video &amp; &lt;Audio&gt;' => 1], self::byName($p['eventCategories']['10']), 'as the tracker stored it');
        $this->assertSame(['Play' => 1], self::byName($p['eventActions']['60']));
        $this->assertSame([], $p['eventCategories']['3']);
        $this->assertFalse($p['typesPartial']);

        // new visits per minute by their first action: V5 11:20, V4 11:50, V3 11:56, V2 11:57:30, V1 11:58
        $this->assertCount(60, $p['visitSparkline']);
        $this->assertSame([1, 1, 1, 0, 0], array_slice($p['visitSparkline'], 55));
        $this->assertSame(1, $p['visitSparkline'][49]);
        $this->assertSame(1, $p['visitSparkline'][19]);
        $this->assertSame(5, array_sum($p['visitSparkline']));

        $this->assertCount(60, $p['sparkline']);
        $this->assertSame(11, array_sum($p['sparkline']));
        $this->assertSame([5, 1, 2, 1, 0], array_slice($p['sparkline'], 55), '11:56 to 12:00');
        $this->assertSame(1, $p['sparkline'][19], '11:20');

        // goal 1 (5) at 11:56:40, order 49.90 at 11:50:30
        $this->assertSame(['3' => 0, '10' => 2, '30' => 2, '60' => 2], $p['conversions']);
        $this->assertSame(['3' => 0.0, '10' => 54.9, '30' => 54.9, '60' => 54.9], $p['revenue']);
        $this->assertSame(['3' => 0, '10' => 1, '30' => 1, '60' => 1], $p['orders']);
        $this->assertSame(49.9, $p['orderRevenue']['60']);
        $this->assertSame([], $p['goals']['3']);
        $this->assertSame([[1, 1, 5.0]], $p['goals']['10']);
        $this->assertSame(1, $p['kVisits']);
        $this->assertSame(1, $p['kActions']);
        $this->assertFalse($r->capHit);
    }

    public function test_cityBelowTheThreshold_isHidden(): void
    {
        $this->assertSame(['3' => [], '10' => [], '30' => [], '60' => []], self::compute(Tier::Slow)->payload['cities']);
    }

    public function test_today_countsSinceLocalMidnight(): void
    {
        $p = self::compute(Tier::Today)->payload;

        $this->assertSame(6, $p['visits']);
        $this->assertSame(12, $p['actions']);
        $this->assertSame(2, $p['conversions']);
        $this->assertSame(54.9, $p['revenue']);
        $this->assertSame(strtotime('2026-03-09 23:00:00 UTC'), $p['since']);
        // kept for the next run: what lies before 11:45 (the settle time), the 2 hits of V5 and V6
        $this->assertSame(self::now() - IncrementalCount::SETTLE_SECONDS, $p['counted']['actions']['until']);
        $this->assertSame(['n' => 2], $p['counted']['actions']['sums']);
        $this->assertSame(['conversions' => 0, 'revenue' => 0.0], $p['counted']['conversions']['sums']);
    }

    public function test_today_goesOnFromTheStateOfTheLastRun(): void
    {
        $now = self::now();
        $state = static fn (array $sums): array => [
            'day' => strtotime('2026-03-09 23:00:00 UTC'), 'until' => $now - 1200, 'full' => $now - 600, 'sums' => $sums,
        ];
        // deliberately wrong bases: the totals show that only the rows from 11:40 were read
        $previous = ['counted' => ['actions' => $state(['n' => 100]), 'conversions' => $state(['conversions' => 7, 'revenue' => 10.0])]];

        $p = self::compute(Tier::Today, null, 0, LiveTrafficFixture::SITE, 0.0, true, $previous)->payload;

        $this->assertSame(6, $p['visits'], 'visits are counted from midnight every time');
        $this->assertSame(110, $p['actions'], 'the base plus the 10 hits from 11:40');
        $this->assertSame(9, $p['conversions'], 'the base plus the order of 11:50:30 and the goal of 11:56:40');
        $this->assertSame(64.9, $p['revenue']);
        $this->assertSame(['n' => 100], $p['counted']['actions']['sums'], 'nothing between 11:40 and 11:45');
    }

    public function test_today_aHitOnTheSettleSecond_isCountedOnce(): void
    {
        // at 12:05 the settled part ends at 11:50:00, the second of V4's pageview; the database ranges include their
        // last second, the incremental count must not
        $now = self::now() + 300;
        $state = static fn (array $sums): array => [
            'day' => strtotime('2026-03-09 23:00:00 UTC'), 'until' => self::now() - 1200, 'full' => $now - 600, 'sums' => $sums,
        ];
        $previous = ['counted' => ['actions' => $state(['n' => 100]), 'conversions' => $state(['conversions' => 7, 'revenue' => 10.0])]];

        $p = self::compute(Tier::Today, null, 0, LiveTrafficFixture::SITE, 0.0, true, $previous, $now)->payload;

        $this->assertSame(110, $p['actions'], 'the base plus the 10 hits from 11:50:00, V4 once');
        $this->assertSame(['n' => 100], $p['counted']['actions']['sums'], 'nothing in [11:40, 11:50)');
        $this->assertSame(9, $p['conversions']);
    }

    public function test_today_onAUsedUpBudget_leavesTheStoredStateAlone(): void
    {
        $old = ['day' => 1, 'until' => 2, 'full' => 3, 'sums' => ['n' => 7]];

        $r = self::compute(Tier::Today, null, 0, LiveTrafficFixture::SITE, 9.0, true, ['counted' => ['actions' => $old]]);

        $this->assertTrue($r->partial);
        $this->assertArrayNotHasKey('actions', $r->payload, 'only the first read runs on a used-up budget');
        $this->assertArrayNotHasKey('counted', $r->payload, 'the stored state stays as it is');
    }

    public function test_siteWithoutTraffic_givesZerosAndNoDelay(): void
    {
        $fast = self::compute(Tier::Fast, null, 0, LiveTrafficFixture::EMPTY_SITE)->payload;
        $this->assertSame(0, $fast['lag']);
        $this->assertSame(0, $fast['activeNow']);
        $this->assertSame(0, $fast['actions5']);
        $this->assertSame(0.0, $fast['revenue5']);
        $this->assertSame(['urls' => [], 'titles' => []], $fast['pagesNow']);

        $slow = self::compute(Tier::Slow, null, 0, LiveTrafficFixture::EMPTY_SITE)->payload;
        $this->assertSame(['3' => 0, '10' => 0, '30' => 0, '60' => 0], $slow['pageviews']);
        $this->assertSame(array_fill(0, 60, 0), $slow['sparkline']);
        $this->assertSame(['3' => [], '10' => [], '30' => [], '60' => []], $slow['goals']);
    }

    public function test_capOne_samplesAndScales(): void
    {
        $r = self::compute(Tier::Fast, new Limits(visitRowCap: 1));

        $this->assertTrue($r->capHit);
        $this->assertSame(3, $r->payload['k']);
        $this->assertSame(0, $r->payload['visits5'] % 3, 'sampled counts are scaled by k');

        $slow = self::compute(Tier::Slow, new Limits(visitRowCap: 1000, actionRowCap: 2));
        $this->assertTrue($slow->capHit);
        $this->assertSame(6, $slow->payload['kActions'], '11 hits in 60 minutes, cap 2');
        $this->assertSame(0, $slow->payload['actions']['60'] % 6);
    }

    public function test_exhaustedBudget_givesPartial(): void
    {
        $r = self::compute(Tier::Fast, null, 0, LiveTrafficFixture::SITE, 9.0);

        $this->assertTrue($r->partial);
        $this->assertSame(2, $r->payload['activeNow'], 'the first read always runs');
        $this->assertArrayNotHasKey('actions5', $r->payload);
    }

    public function test_level2_dropsTheCities(): void
    {
        $this->assertSame(['3' => [], '10' => [], '30' => [], '60' => []], self::compute(Tier::Slow, new Limits(cityMinVisits: 1), 2)->payload['cities']);
    }

    public function test_level3_computesCountersOnly(): void
    {
        $p = self::compute(Tier::Fast, null, 3)->payload;

        $this->assertSame(2, $p['activeNow']);
        $this->assertSame(3, $p['visits5']);
        $this->assertSame(9, $p['actions5']);
        $this->assertArrayNotHasKey('pagesNow', $p);
    }

    public function test_statementTimeLimit_endsInQueryTimeout(): void
    {
        // a real long read: MySQL ends SLEEP() at its limit without an error, a long SELECT with error 3024 (MariaDB 1969)
        $this->expectException(QueryTimeout::class);
        (new DbLogReader(new Limits(statementSeconds: 1)))->scalar([
            'sql' => 'SELECT COUNT(*) AS n FROM information_schema.columns a, information_schema.columns b, information_schema.columns c',
            'bind' => [],
        ]);
    }
}

TierComputerTest::$fixture = new LiveTrafficFixture();
