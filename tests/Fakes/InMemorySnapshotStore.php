<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes;

use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeStates;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\PayloadCodec;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\Snapshot;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\SnapshotStore;

/**
 * Keeps rows the way the database table does (encoded payload blobs), so undecodable rows can be tested.
 */
final class InMemorySnapshotStore implements SnapshotStore
{
    /** @var array<string, array{computedAt: int, dataUntil: int, blob: string}> key "idSite|viewId|tier" */
    public array $rows = [];
    public int $writes = 0;

    public function readAll(int $idSite, int $viewId): array
    {
        $snapshots = [];
        $states = new DegradeStates();
        foreach ($this->rows as $key => $row) {
            [$site, $view, $tier] = explode('|', $key);
            if ((int) $site !== $idSite || (int) $view !== $viewId) {
                continue;
            }
            $payload = PayloadCodec::decode($row['blob']);
            if ($payload === null) {
                continue;
            }
            if ($tier === 'state') {
                $states = DegradeStates::fromArray($payload);
            } elseif (Tier::tryFrom($tier) !== null) {
                $snapshots[$tier] = new Snapshot($idSite, $viewId, Tier::from($tier), $row['computedAt'], $row['dataUntil'], $payload);
            }
        }
        return ['snapshots' => $snapshots, 'states' => $states];
    }

    public function write(Snapshot $snapshot): void
    {
        $this->writes++;
        $this->rows[$snapshot->idSite . '|' . $snapshot->viewId . '|' . $snapshot->tier->value] = [
            'computedAt' => $snapshot->computedAt,
            'dataUntil' => $snapshot->dataUntil,
            'blob' => PayloadCodec::encode($snapshot->payload),
        ];
    }

    public function writeState(int $idSite, int $viewId, DegradeStates $states, int $now): void
    {
        $this->rows[$idSite . '|' . $viewId . '|state'] = [
            'computedAt' => $now,
            'dataUntil' => $now,
            'blob' => PayloadCodec::encode($states->toArray()),
        ];
    }

    public function countActiveKeys(int $since): int
    {
        $keys = [];
        foreach ($this->rows as $key => $row) {
            [$site, $view, $tier] = explode('|', $key);
            if ($tier === Tier::Fast->value && $row['computedAt'] >= $since) {
                $keys[$site . '|' . $view] = true;
            }
        }
        return count($keys);
    }

    public function deleteNotComputedSince(int $timestamp): int
    {
        $before = count($this->rows);
        $this->rows = array_filter($this->rows, static fn (array $row): bool => $row['computedAt'] >= $timestamp);
        return $before - count($this->rows);
    }

    /** A row as a broken or foreign writer might have left it. */
    public function putRaw(int $idSite, int $viewId, string $tier, int $computedAt, string $blob): void
    {
        $this->rows[$idSite . '|' . $viewId . '|' . $tier] = ['computedAt' => $computedAt, 'dataUntil' => $computedAt, 'blob' => $blob];
    }
}
