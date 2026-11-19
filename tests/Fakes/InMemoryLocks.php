<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes;

use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\LockHandle;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\Locks;

final class InMemoryLocks implements Locks
{
    /** @var array<string, int> key => ttl */
    public array $held = [];
    /** @var string[] every key ever acquired, in order */
    public array $acquired = [];

    public function tryAcquire(string $key, int $ttl): ?LockHandle
    {
        if (isset($this->held[$key])) {
            return null;
        }
        $this->held[$key] = $ttl;
        $this->acquired[] = $key;
        $release = function () use ($key): void {
            unset($this->held[$key]);
        };
        return new class ($release) implements LockHandle {
            /** @var callable */
            private $release;

            public function __construct(callable $release)
            {
                $this->release = $release;
            }

            public function release(): void
            {
                ($this->release)();
            }
        };
    }

    /** Another request holds this key. */
    public function hold(string $key): void
    {
        $this->held[$key] = 60;
    }
}
