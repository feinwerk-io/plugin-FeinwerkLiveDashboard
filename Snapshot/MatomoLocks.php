<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Snapshot;

use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Container\StaticContainer;

/**
 * Locks in Matomo's lock table (MySqlLockBackend by default), so single flight and the gate hold across web nodes. A
 * lock expires after its TTL, so a crashed request frees it. Keys stay short: Lock hashes keys above 70 characters
 * and then loses the namespace.
 */
final class MatomoLocks implements Locks
{
    public const NAMESPACE = 'fwLiveDashboard';

    public function __construct(private readonly ?LockBackend $backend = null)
    {
    }

    public function tryAcquire(string $key, int $ttl): ?LockHandle
    {
        $lock = new Lock($this->backend ?? StaticContainer::get(LockBackend::class), self::NAMESPACE);
        if (!$lock->acquireLock($key, $ttl)) {
            return null;
        }
        return new class ($lock) implements LockHandle {
            public function __construct(private readonly Lock $lock)
            {
            }

            public function release(): void
            {
                $this->lock->unlock();
            }
        };
    }
}
