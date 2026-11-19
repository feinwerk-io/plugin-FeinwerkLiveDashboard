<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Snapshot;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeStates;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;

/**
 * Snapshots in the table fw_livedashboard_snapshot (concept 3.3): the database is the one thing all web nodes share,
 * Matomo's default cache is per node. Written and read on the primary (Db::get()), one REPLACE per refresh, read by
 * primary key prefix. The degrade states of a key (one per tier) are the row with tier "state".
 */
final class DbSnapshotStore implements SnapshotStore
{
    public const TABLE = 'fw_livedashboard_snapshot';
    private const STATE = 'state';

    public static function tableName(): string
    {
        return Common::prefixTable(self::TABLE);
    }

    public function install(): void
    {
        DbHelper::createTable(self::TABLE, '
            `idsite` INT UNSIGNED NOT NULL,
            `view_id` INT UNSIGNED NOT NULL,
            `tier` VARCHAR(8) NOT NULL,
            `computed_at` INT UNSIGNED NOT NULL,
            `data_until` INT UNSIGNED NOT NULL,
            `payload` MEDIUMBLOB NOT NULL,
            PRIMARY KEY (`idsite`, `view_id`, `tier`)
        ');
    }

    public function uninstall(): void
    {
        Db::dropTables([self::tableName()]);
    }

    public function readAll(int $idSite, int $viewId): array
    {
        $rows = Db::fetchAll(
            'SELECT tier, computed_at, data_until, payload FROM ' . self::tableName() . ' WHERE idsite = ? AND view_id = ?',
            [$idSite, $viewId]
        );
        $snapshots = [];
        $states = new DegradeStates();
        foreach ($rows as $row) {
            $payload = PayloadCodec::decode((string) $row['payload']);
            if ($payload === null) {
                continue;
            }
            if ($row['tier'] === self::STATE) {
                $states = DegradeStates::fromArray($payload);
                continue;
            }
            $tier = Tier::tryFrom((string) $row['tier']);
            if ($tier !== null) {
                $snapshots[$tier->value] = new Snapshot($idSite, $viewId, $tier, (int) $row['computed_at'], (int) $row['data_until'], $payload);
            }
        }
        uksort($snapshots, static fn (string $a, string $b): int => self::order($a) <=> self::order($b));
        return ['snapshots' => $snapshots, 'states' => $states];
    }

    public function write(Snapshot $snapshot): void
    {
        $this->replace($snapshot->idSite, $snapshot->viewId, $snapshot->tier->value, $snapshot->computedAt, $snapshot->dataUntil, $snapshot->payload);
    }

    public function writeState(int $idSite, int $viewId, DegradeStates $states, int $now): void
    {
        $this->replace($idSite, $viewId, self::STATE, $now, $now, $states->toArray());
    }

    public function countActiveKeys(int $since): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . self::tableName() . ' WHERE tier = ? AND computed_at >= ?',
            [Tier::Fast->value, $since]
        );
    }

    public function deleteNotComputedSince(int $timestamp): int
    {
        $statement = Db::query('DELETE FROM ' . self::tableName() . ' WHERE computed_at < ?', [$timestamp]);
        return (int) $statement->rowCount();
    }

    private function replace(int $idSite, int $viewId, string $tier, int $computedAt, int $dataUntil, array $payload): void
    {
        Db::query(
            'REPLACE INTO ' . self::tableName() . ' (idsite, view_id, tier, computed_at, data_until, payload) VALUES (?, ?, ?, ?, ?, ?)',
            [$idSite, $viewId, $tier, $computedAt, max(0, $dataUntil), PayloadCodec::encode($payload)]
        );
    }

    private static function order(string $tier): int
    {
        foreach (Tier::ordered() as $i => $candidate) {
            if ($candidate->value === $tier) {
                return $i;
            }
        }
        return PHP_INT_MAX;
    }
}
