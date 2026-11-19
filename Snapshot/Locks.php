<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Snapshot;

/**
 * Non-blocking locks shared by all web nodes (Matomo's lock table): single flight per tier and the concurrency gate.
 */
interface Locks
{
    /** @return LockHandle|null null when another request holds the key */
    public function tryAcquire(string $key, int $ttl): ?LockHandle;
}
