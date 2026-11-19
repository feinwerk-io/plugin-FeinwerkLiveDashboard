<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeState;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeStates;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;
use Piwik\Plugins\FeinwerkLiveDashboard\FeinwerkLiveDashboard;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\DbSnapshotStore;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\Snapshot;
use Piwik\Plugins\FeinwerkLiveDashboard\Tasks;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The snapshot table against a real database.
 *
 * @group FeinwerkLiveDashboard
 */
class SnapshotStoreTest extends IntegrationTestCase
{
    private const NOW = 1795000000;

    private DbSnapshotStore $store;

    public function setUp(): void
    {
        parent::setUp();
        (new FeinwerkLiveDashboard())->install();
        Db::query('TRUNCATE TABLE ' . DbSnapshotStore::tableName());
        $this->store = new DbSnapshotStore();
    }

    public function test_readAll_returnsTheTiersAndTheStateOfOneKey(): void
    {
        $payload = ['activeNow' => 3, 'pagesNowUrls' => [[17, 4]], 'title' => 'Größe <b>'];
        $this->store->write(new Snapshot(1, 0, Tier::Fast, self::NOW, self::NOW - 2, $payload));
        $this->store->write(new Snapshot(1, 0, Tier::Slow, self::NOW - 30, self::NOW - 31, ['visits30' => 9]));
        $this->store->write(new Snapshot(2, 0, Tier::Fast, self::NOW, self::NOW, ['activeNow' => 99]));
        $this->store->write(new Snapshot(1, 1, Tier::Fast, self::NOW, self::NOW, ['activeNow' => 98]));
        $states = (new DegradeStates())->with(Tier::Slow, new DegradeState(2, 1, 0, 0));
        $this->store->writeState(1, 0, $states, self::NOW);

        $all = $this->store->readAll(1, 0);

        $this->assertSame(['fast', 'slow'], array_keys($all['snapshots']));
        $fast = $all['snapshots']['fast'];
        $this->assertSame(Tier::Fast, $fast->tier);
        $this->assertSame(self::NOW, $fast->computedAt);
        $this->assertSame(self::NOW - 2, $fast->dataUntil);
        $this->assertSame($payload, $fast->payload);
        $this->assertSame(['visits30' => 9], $all['snapshots']['slow']->payload);
        $this->assertEquals($states, $all['states']);
    }

    public function test_readAll_ofAnUnknownKey_isEmptyWithLevel0(): void
    {
        $all = $this->store->readAll(5, 0);
        $this->assertSame([], $all['snapshots']);
        $this->assertSame(0, $all['states']->level());
    }

    public function test_write_replacesTheRow(): void
    {
        $this->store->write(new Snapshot(1, 0, Tier::Fast, self::NOW - 10, self::NOW - 10, ['activeNow' => 1]));
        $this->store->write(new Snapshot(1, 0, Tier::Fast, self::NOW, self::NOW, ['activeNow' => 2]));

        $this->assertSame(['activeNow' => 2], $this->store->readAll(1, 0)['snapshots']['fast']->payload);
        $this->assertSame(1, (int) Db::fetchOne('SELECT COUNT(*) FROM ' . DbSnapshotStore::tableName()));
    }

    public function test_undecodableRow_isLeftOut(): void
    {
        Db::query(
            'INSERT INTO ' . DbSnapshotStore::tableName() . ' (idsite, view_id, tier, computed_at, data_until, payload) VALUES (?, ?, ?, ?, ?, ?)',
            [1, 0, 'fast', self::NOW, self::NOW, 'not gzip']
        );
        $this->store->write(new Snapshot(1, 0, Tier::Slow, self::NOW, self::NOW, ['visits30' => 1]));

        $this->assertSame(['slow'], array_keys($this->store->readAll(1, 0)['snapshots']));
    }

    public function test_countActiveKeys_countsFastRowsSince(): void
    {
        $this->store->write(new Snapshot(1, 0, Tier::Fast, self::NOW - 10, self::NOW, []));
        $this->store->write(new Snapshot(1, 0, Tier::Slow, self::NOW - 10, self::NOW, []));
        $this->store->write(new Snapshot(2, 0, Tier::Fast, self::NOW - 400, self::NOW, []));
        $this->store->write(new Snapshot(3, 0, Tier::Slow, self::NOW, self::NOW, []));
        $this->store->write(new Snapshot(4, 1, Tier::Fast, self::NOW, self::NOW, []));

        $this->assertSame(2, $this->store->countActiveKeys(self::NOW - 300));
    }

    public function test_deleteNotComputedSince_keepsRecentRows(): void
    {
        $this->store->write(new Snapshot(1, 0, Tier::Fast, self::NOW - 90000, self::NOW, []));
        $this->store->writeState(1, 0, new DegradeStates(), self::NOW - 90000);
        $this->store->write(new Snapshot(2, 0, Tier::Fast, self::NOW, self::NOW, []));

        $this->assertSame(2, $this->store->deleteNotComputedSince(self::NOW - 86400));
        $this->assertSame([], $this->store->readAll(1, 0)['snapshots']);
        $this->assertCount(1, $this->store->readAll(2, 0)['snapshots']);
    }

    public function test_dailyTask_deletesRowsNotComputedForADay(): void
    {
        $this->store->write(new Snapshot(1, 0, Tier::Fast, time() - 90000, time(), []));
        $this->store->write(new Snapshot(2, 0, Tier::Fast, time() - 60, time(), []));

        (new Tasks())->deleteOldSnapshots();

        $this->assertSame([], $this->store->readAll(1, 0)['snapshots']);
        $this->assertCount(1, $this->store->readAll(2, 0)['snapshots']);
    }

    public function test_table_isListedAsInstalled(): void
    {
        $tables = [];
        Piwik::postEvent('Db.getTablesInstalled', [&$tables]);
        $this->assertContains(Common::prefixTable('fw_livedashboard_snapshot'), $tables);
    }

    public function test_uninstall_dropsTheTable(): void
    {
        (new FeinwerkLiveDashboard())->uninstall();
        $this->assertSame([], Db::fetchAll('SHOW TABLES LIKE ?', [DbSnapshotStore::tableName()]));
        (new FeinwerkLiveDashboard())->install();
        $this->assertCount(1, Db::fetchAll('SHOW TABLES LIKE ?', [DbSnapshotStore::tableName()]));
    }
}
