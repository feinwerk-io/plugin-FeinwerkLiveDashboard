<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard;

use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\DbSnapshotStore;

class Tasks extends \Piwik\Plugin\Tasks
{
    /** Snapshots nobody polled for a day: computing happens only when someone watches, so polls never write. */
    public const KEEP_SECONDS = 86400;

    public function schedule()
    {
        $this->daily('deleteOldSnapshots');
    }

    public function deleteOldSnapshots(): void
    {
        (new DbSnapshotStore())->deleteNotComputedSince(time() - self::KEEP_SECONDS);
    }
}
