<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Integration;

use Piwik\API\Request;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\NoAccessException;
use Piwik\Plugins\FeinwerkLiveDashboard\API;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Clock;
use Piwik\Plugins\FeinwerkLiveDashboard\FeinwerkLiveDashboard;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\DbSnapshotStore;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes\FixedClock;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fixtures\LiveTrafficFixture;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * FeinwerkLiveDashboard.getSnapshot with the fixture's fixed "now".
 *
 * @group FeinwerkLiveDashboard
 */
class ApiSnapshotTest extends IntegrationTestCase
{
    /** @var LiveTrafficFixture */
    public static $fixture = null;

    public function provideContainerConfig()
    {
        return [
            'Piwik\\Access' => new FakeAccess(),
            Clock::class => new FixedClock(strtotime(LiveTrafficFixture::NOW . ' UTC')),
        ];
    }

    public function setUp(): void
    {
        parent::setUp();
        FakeAccess::$superUser = true;
        (new FeinwerkLiveDashboard())->install();
        Db::query('TRUNCATE TABLE ' . DbSnapshotStore::tableName());
        Fixture::loadAllTranslations();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
        parent::tearDown();
    }

    private static function rowCount(): int
    {
        return (int) Db::fetchOne('SELECT COUNT(*) FROM ' . DbSnapshotStore::tableName());
    }

    public function test_withoutViewAccess_isRefusedBeforeAnyRead(): void
    {
        FakeAccess::clearAccess(false, [], [], 'anonymous');
        try {
            API::getInstance()->getSnapshot(1);
            $this->fail('access error expected');
        } catch (NoAccessException $e) {
            $this->assertSame(0, self::rowCount(), 'nothing computed');
        }
    }

    public function test_viewerOfAnotherSite_isRefused(): void
    {
        FakeAccess::clearAccess(false, [], [2], 'viewer');
        $this->expectException(NoAccessException::class);
        API::getInstance()->getSnapshot(1);
    }

    public function test_viewAccess_isEnough(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');
        $this->assertSame(2, API::getInstance()->getSnapshot(1)['payload']['fast']['activeNow']);
    }

    public function test_otherLiveView_isRefused(): void
    {
        $this->expectExceptionMessage('This live view does not exist.');
        API::getInstance()->getSnapshot(1, 1);
    }

    public function test_firstCall_computesEveryTier(): void
    {
        $r = API::getInstance()->getSnapshot(1);

        $this->assertFalse($r['notModified']);
        $this->assertSame(2, $r['payload']['fast']['activeNow']);
        $this->assertSame(['3' => 2, '10' => 4, '30' => 4, '60' => 5], $r['payload']['slow']['visits']);
        $this->assertSame(54.9, $r['payload']['today']['revenue']);
        $this->assertSame(['fast' => 0, 'slow' => 0, 'today' => 0], $r['ages']);
        $this->assertSame(0, $r['level']);
        $this->assertGreaterThanOrEqual(10, $r['nextPollSeconds']);
        $this->assertLessThanOrEqual(12, $r['nextPollSeconds']);
        $this->assertSame(0, $r['lag'], 'no reader configured');
        $this->assertFalse($r['delayed']);
        $this->assertFalse($r['stale']);
        $this->assertSame(strtotime(LiveTrafficFixture::NOW . ' UTC'), $r['generatedAt']);
        $this->assertNotSame('', $r['version']);
        $this->assertSame(['fast', 'slow', 'today'], array_keys($r['computedMs']));
    }

    public function test_unchangedVersion_isNotModified(): void
    {
        $api = API::getInstance();
        $r = $api->getSnapshot(1);

        $again = $api->getSnapshot(1, 0, $r['version']);

        $this->assertTrue($again['notModified']);
        $this->assertSame([], $again['computedMs']);
        $this->assertSame([], $again['payload']);
        $this->assertSame($r['version'], $again['version']);
    }

    public function test_paddedParameters_behaveLikeTrimmedOnes(): void
    {
        $r = Request::processRequest('FeinwerkLiveDashboard.getSnapshot', ['idSite' => ' 1 ', 'view' => ' 0 ', 'since' => ' ', 'format' => 'original']);
        $this->assertSame(2, $r['payload']['fast']['activeNow']);
    }

    public function test_json_carriesTheSameDataAsTheArray(): void
    {
        $original = Request::processRequest('FeinwerkLiveDashboard.getSnapshot', ['idSite' => 1, 'format' => 'original']);
        $json = Request::processRequest('FeinwerkLiveDashboard.getSnapshot', ['idSite' => 1, 'format' => 'json']);
        $decoded = json_decode((string) $json, true);
        $this->assertIsArray($decoded, (string) $json);

        // the second call only reads (same version); the poll interval has its own jitter
        unset($original['nextPollSeconds'], $decoded['nextPollSeconds'], $original['computedMs'], $decoded['computedMs']);
        $this->assertEquals($original, $decoded);
        $this->assertSame('Video & <Audio>', $decoded['payload']['slow']['eventCategories']['10'][0]['label'], 'plain text, not escaped twice');
        $this->assertSame([], $decoded['payload']['slow']['cities']['3'], 'an empty list stays a list');
        $this->assertSame([], $decoded['payload']['slow']['goals']['3'], 'an empty list of a window stays a list');
        $this->assertCount(60, $decoded['payload']['slow']['sparkline']);
    }

    public function test_dataRounding_roundsEveryCount(): void
    {
        $policy = 'Piwik\Policy\CnilPolicy';
        if (!class_exists('Piwik\Plugins\PrivacyManager\DataRounding') || !class_exists($policy) || !method_exists($policy, 'setActiveStatus')) {
            $this->markTestSkipped('data rounding exists from Matomo 5.14 on');
        }
        $policy::setActiveStatus(1, true);
        try {
            $p = API::getInstance()->getSnapshot(1)['payload'];
        } finally {
            $policy::setActiveStatus(1, false);
        }

        // Matomo rounds to tens with at least ten for any count above zero
        $this->assertSame(10, $p['fast']['activeNow']);
        $this->assertSame(10, $p['fast']['countriesNow'][0]['count']);
        $this->assertSame(['3' => 10, '10' => 10, '30' => 10, '60' => 10], $p['slow']['visits']);
        $this->assertSame(['id' => 1, 'label' => 'Newsletter', 'conversions' => 10, 'revenue' => 5.0], $p['slow']['goals']['10'][0]);
        $this->assertSame(10, $p['today']['actions']);
        $this->assertSame(54.9, $p['today']['revenue'], 'revenue is no count');
        $this->assertSame(0, $p['slow']['sparkline'][0], 'zero stays zero');
        $this->assertArrayNotHasKey('newestVisits', $p['fast'], 'no single visits with data rounding');
    }

    public function test_liveLog_comesWithTheVisitsLogOnly(): void
    {
        $visits = API::getInstance()->getSnapshot(1)['payload']['fast']['newestVisits'];
        $this->assertCount(3, $visits);
        $this->assertSame(['Austria', 'Search Engines', 'Google', 'Smartphone', 'shop.example/home'], [
            $visits[1]['countryLabel'], $visits[1]['channel'], $visits[1]['source'], $visits[1]['device'], $visits[1]['page'],
        ]);
        $this->assertArrayNotHasKey('idvisitor', $visits[0]);
        $this->assertSame(['goal'], $visits[2]['icons']);
        // the fixture shop has ecommerce: four places of Matomo's own icons
        $this->assertSame(['goal', 'order', 'cart', 'returning'], array_column(API::getInstance()->getSnapshot(1)['logIcons'], 'key'));
        $this->assertSame('plugins/Morpheus/images/goal.svg', API::getInstance()->getSnapshot(1)['logIcons'][0]['src']);

        // switched off in the website settings: the stored live log is not given out, and not computed again
        $settings = new \Piwik\Plugins\Live\MeasurableSettings(1);
        $settings->disableVisitorLog->setValue(true);
        $settings->save();
        $this->assertArrayNotHasKey('newestVisits', API::getInstance()->getSnapshot(1)['payload']['fast']);
        Db::query('TRUNCATE TABLE ' . DbSnapshotStore::tableName());
        API::getInstance()->getSnapshot(1);
        $stored = (new DbSnapshotStore())->readAll(1, 0)['snapshots']['fast']->payload;
        $this->assertArrayNotHasKey('newestVisits', $stored);
    }

    public function test_otherPlugins_addIconPlaces(): void
    {
        \Piwik\Piwik::addAction('FeinwerkLiveDashboard.getLiveLogIcons', static function (array &$icons, int $idSite): void {
            $icons[] = ['key' => 'crash', 'src' => 'plugins/Other/images/crash.svg', 'label' => 'Crash ' . $idSite];
            $icons[] = ['key' => 'outside', 'src' => 'https://example.org/x.svg', 'label' => 'refused'];
        });

        $icons = API::getInstance()->getSnapshot(1)['logIcons'];

        $this->assertSame(['goal', 'order', 'cart', 'returning', 'crash'], array_column($icons, 'key'));
        $this->assertSame('Crash 1', $icons[4]['label']);
    }

    public function test_labels_followTheLanguage(): void
    {
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage('de');

        $fast = API::getInstance()->getSnapshot(1)['payload']['fast'];

        $this->assertSame(['id' => 'at', 'label' => 'Österreich', 'count' => 1], $fast['countriesNow'][0]);
        $this->assertSame(['id' => 2, 'label' => 'Suchmaschinen', 'count' => 1], $fast['channelsNow'][0]);
        $this->assertSame(['id' => 6, 'label' => 'Kampagnen', 'count' => 1], $fast['channelsNow'][1]);
    }

    public function test_goalNames_comeFromTheSite(): void
    {
        $slow = API::getInstance()->getSnapshot(1)['payload']['slow'];

        $this->assertSame([['id' => 1, 'label' => 'Newsletter', 'conversions' => 1, 'revenue' => 5.0]], $slow['goals']['30']);
        $this->assertSame([['id' => 0, 'label' => 'Desktop', 'count' => 3], ['id' => 1, 'label' => 'Smartphone', 'count' => 1]], $slow['devices']['30']);
    }
}

ApiSnapshotTest::$fixture = new LiveTrafficFixture();
