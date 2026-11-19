<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Load;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\load\ShopCatalog;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\load\TrafficModel;

/**
 * The traffic model of the load test (tests/load/replay.php): realistic shares, reproducible by seed.
 *
 * @group FeinwerkLiveDashboard
 */
class TrafficModelTest extends TestCase
{
    private static function visits(int $count, int $seed = 7): array
    {
        $model = new TrafficModel($seed);
        $visits = [];
        for ($i = 0; $i < $count; $i++) {
            $visits[] = $model->visit(5);
        }
        return $visits;
    }

    public function test_sameSeed_givesTheSameVisits(): void
    {
        $this->assertSame(self::visits(50, 11), self::visits(50, 11));
        $this->assertNotSame(self::visits(50, 11), self::visits(50, 12));
    }

    public function test_aVisit_isOnePersonWithAscendingHits(): void
    {
        foreach (self::visits(300) as $visit) {
            $this->assertSame(0, $visit[0]['offset']);
            $this->assertArrayHasKey('action_name', $visit[0]['params'], 'a visit starts with a pageview');
            $first = $visit[0]['params'];
            $previous = -1;
            foreach ($visit as $hit) {
                $this->assertGreaterThan($previous, $hit['offset']);
                $previous = $hit['offset'];
                foreach (['idsite', 'rec', 'cip', '_id', 'ua', 'country'] as $key) {
                    $this->assertSame($first[$key], $hit['params'][$key], $key);
                }
                $this->assertSame(5, $hit['params']['idsite']);
                $this->assertSame(1, preg_match('/^[0-9a-f]{16}$/', $hit['params']['_id']));
            }
        }
    }

    public function test_everyVisit_hasACityWithItsRegion(): void
    {
        $regions = [];
        foreach (self::visits(300) as $visit) {
            $first = $visit[0]['params'];
            foreach (['city', 'region', 'lat', 'long'] as $key) {
                $this->assertArrayHasKey($key, $first, 'one consistent place: the map shows it, no IP lookup guesses another');
            }
            $regions[$first['country'] . '|' . $first['region']] = true;
        }
        $this->assertArrayHasKey('de|BY', $regions);
        $this->assertGreaterThan(10, count($regions), 'several regions per country');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function languages(): array
    {
        return ['English' => ['en'], 'German' => ['de']];
    }

    /**
     * Gallery shots need a shop that looks real: named pages with titles, real search engines and networks, site search
     * terms and files of the shop, in the language of the shot.
     *
     * @dataProvider languages
     */
    public function test_aShopCatalog_givesRealLookingPagesAndSources(string $language): void
    {
        $catalog = ShopCatalog::get($language);
        $model = new TrafficModel(3, 400, $catalog);
        $paths = array_column($catalog['pages'], 0);
        $titles = array_column($catalog['pages'], 1);
        $referrers = [];
        foreach (range(1, 400) as $ignored) {
            foreach ($model->visit(5) as $hit) {
                $p = $hit['params'];
                $this->assertStringStartsWith($catalog['site'], $p['url']);
                $this->assertSame(0, preg_match('~/p/\d|Page \d|keyword \d|partner\d*\.example|/doc\d~', json_encode($p)), json_encode($p));
                if (isset($p['action_name'])) {
                    $this->assertContains($p['action_name'], $titles);
                    $this->assertContains((string) parse_url($p['url'], PHP_URL_PATH), $paths);
                }
                if (isset($p['search'])) {
                    $this->assertContains($p['search'], $catalog['searchTerms']);
                }
                if (isset($p['urlref'])) {
                    $referrers[(string) parse_url($p['urlref'], PHP_URL_HOST)] = true;
                }
            }
        }
        $this->assertNotEmpty(preg_grep('/^www\.google\./', array_keys($referrers)), 'Google in the language\'s country version');
        $this->assertGreaterThanOrEqual(5, count($referrers), 'several search engines, networks and websites');
    }

    public function test_hits_averageAboutThreePerVisit(): void
    {
        $visits = self::visits(3000);
        $mean = array_sum(array_map('count', $visits)) / count($visits);
        $this->assertGreaterThan(2.6, $mean);
        $this->assertLessThan(3.4, $mean);
    }

    public function test_conversionsAndOrders_haveTheirShares(): void
    {
        $goals = 0;
        $orders = 0;
        $campaigns = 0;
        $visits = self::visits(10000);
        foreach ($visits as $visit) {
            foreach ($visit as $hit) {
                $idGoal = $hit['params']['idgoal'] ?? null;
                if ($idGoal === 0) {
                    $orders++;
                    $this->assertArrayHasKey('ec_id', $hit['params']);
                } elseif ($idGoal !== null) {
                    $goals++;
                }
            }
            if (str_contains($visit[0]['params']['url'], 'mtm_campaign=')) {
                $campaigns++;
            }
        }
        $this->assertEqualsWithDelta(300, $goals, 90, 'about 3 % of visits convert a goal');
        $this->assertEqualsWithDelta(100, $orders, 50, 'about 1 % of visits order');
        $this->assertEqualsWithDelta(1500, $campaigns, 300, 'about 15 % come from campaigns');
    }

    public function test_hitKinds_coverEventsDownloadsOutlinksAndSearch(): void
    {
        $kinds = [];
        foreach (self::visits(2000) as $visit) {
            foreach ($visit as $hit) {
                $p = $hit['params'];
                $kind = isset($p['e_c']) ? 'event' : (isset($p['download']) ? 'download' : (isset($p['link']) ? 'outlink'
                    : (isset($p['search']) ? 'search' : (isset($p['idgoal']) ? 'conversion' : 'pageview'))));
                $kinds[$kind] = ($kinds[$kind] ?? 0) + 1;
            }
        }
        foreach (['pageview', 'event', 'download', 'outlink', 'search', 'conversion'] as $kind) {
            $this->assertGreaterThan(0, $kinds[$kind] ?? 0, $kind);
        }
        $this->assertGreaterThan(0.7, $kinds['pageview'] / array_sum($kinds));
    }
}
