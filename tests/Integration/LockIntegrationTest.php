<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Integration;

use Piwik\Db;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Limits;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;
use Piwik\Plugins\FeinwerkLiveDashboard\FeinwerkLiveDashboard;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\DbSnapshotStore;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\MatomoLocks;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\Snapshot;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\SnapshotService;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes\FakeTierComputer;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes\FixedClock;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Single flight and the concurrency gate through Matomo's lock table, shared by every web node.
 *
 * @group FeinwerkLiveDashboard
 */
class LockIntegrationTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        (new FeinwerkLiveDashboard())->install();
        Db::query('TRUNCATE TABLE ' . DbSnapshotStore::tableName());
    }

    public function test_aHeldKey_cannotBeTakenUntilReleased(): void
    {
        $locks = new MatomoLocks();
        $first = $locks->tryAcquire('s1v0fast', 18);
        $this->assertNotNull($first);
        $this->assertNull((new MatomoLocks())->tryAcquire('s1v0fast', 18));
        $this->assertNotNull($other = (new MatomoLocks())->tryAcquire('s2v0fast', 18), 'other keys are free');

        $first->release();
        $other->release();
        $again = (new MatomoLocks())->tryAcquire('s1v0fast', 18);
        $this->assertNotNull($again);
        $again->release();
    }

    public function test_secondComputationWhileLocked_servesStale(): void
    {
        $now = time();
        $store = new DbSnapshotStore();
        $store->write(new Snapshot(1, 0, Tier::Fast, $now - 20, $now - 20, ['activeNow' => 3]));
        $store->write(new Snapshot(1, 0, Tier::Slow, $now, $now, ['visits30' => 9]));
        $store->write(new Snapshot(1, 0, Tier::Today, $now, $now, ['visits' => 90]));
        $computer = new FakeTierComputer();
        $service = new SnapshotService($store, new MatomoLocks(), $computer, new FixedClock($now), new Limits());

        $held = (new MatomoLocks())->tryAcquire('s1v0fast', 18);
        $result = $service->poll(1, 0, 'UTC', true, '');
        $held->release();

        $this->assertSame([], $computer->computedTiers());
        $this->assertSame(['activeNow' => 3], $result->payload['fast']);

        $service->poll(1, 0, 'UTC', true, '');
        $this->assertSame(['fast'], $computer->computedTiers(), 'computed once the lock is free');
        $this->assertSame(1, $store->readAll(1, 0)['snapshots']['fast']->payload['fastValue']);
    }

    public function test_fullGate_servesStale(): void
    {
        $gate = [(new MatomoLocks())->tryAcquire('gate1', 18), (new MatomoLocks())->tryAcquire('gate2', 18)];
        $computer = new FakeTierComputer();
        $service = new SnapshotService(new DbSnapshotStore(), new MatomoLocks(), $computer, new FixedClock(time()), new Limits());

        $service->poll(1, 0, 'UTC', true, '');

        $this->assertSame([], $computer->computedTiers());
        foreach ($gate as $slot) {
            $slot->release();
        }
        $this->assertNotNull($free = (new MatomoLocks())->tryAcquire('s1v0fast', 18), 'the single-flight key was released');
        $free->release();
    }
}
