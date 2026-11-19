<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Snapshot;

use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeStates;

/**
 * Where snapshots live. The database implementation is shared by all web nodes, so one computation serves every viewer
 * (concept 3.3). Rows whose payload does not decode are left out of readAll(), so they are computed again.
 */
interface SnapshotStore
{
    /**
     * One primary key prefix read for a website and view.
     *
     * @return array{snapshots: array<string, Snapshot>, states: DegradeStates} snapshots keyed by tier value
     */
    public function readAll(int $idSite, int $viewId): array;

    public function write(Snapshot $snapshot): void;

    public function writeState(int $idSite, int $viewId, DegradeStates $states, int $now): void;

    /** Distinct website and view keys with a fast snapshot computed since the timestamp. */
    public function countActiveKeys(int $since): int;

    /** Deletes every row not computed since the timestamp; returns the number of rows deleted. */
    public function deleteNotComputedSince(int $timestamp): int;
}
