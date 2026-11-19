<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Snapshot;

use Piwik\Plugins\FeinwerkLiveDashboard\Data\QueryTimeout;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Budget;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Clock;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeController;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeState;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeStates;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Limits;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\RunOutcome;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Schedule;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;

/**
 * One shared snapshot per website, live view and tier, computed by whichever poll finds it stale and read by every
 * viewer (concept 3.1 to 3.3, 3.8, 3.9). A poll reads the stored rows once and computes every stale tier in order (fast,
 * slow, today) under one shared budget and one slot of the instance-wide concurrency gate; a tier another request is
 * computing (single flight) is skipped. A full gate, a used-up budget or a statement timeout ends the computing, and the
 * poll answers with what is stored.
 */
final class SnapshotService
{
    /** A key whose fast tier was computed within this time counts as active (concept 3.7, "active keys"). */
    public const ACTIVE_SECONDS = 300;
    public const LOCK_MARGIN_SECONDS = 10;
    public const MAX_JITTER = 0.2;

    /** @var callable fn(): float in [0, 1] */
    private $random;

    public function __construct(
        private readonly SnapshotStore $store,
        private readonly Locks $locks,
        private readonly TierComputer $computer,
        private readonly Clock $clock,
        private readonly Limits $limits,
        ?callable $random = null
    ) {
        $this->random = $random ?? static fn (): float => mt_rand() / mt_getrandmax();
    }

    public function poll(int $idSite, int $viewId, string $timezone, bool $visitorLogEnabled, string $since): PollResult
    {
        $now = $this->clock->now();
        $stored = $this->store->readAll($idSite, $viewId);
        /** @var array<string, Snapshot> $snapshots */
        $snapshots = $stored['snapshots'];
        /** @var DegradeStates $states */
        $states = $stored['states'];
        $budget = new Budget($this->clock, $this->limits->budgetSeconds);
        $slot = null;
        $computed = false;
        $computedMs = [];

        try {
            foreach (Tier::ordered() as $tier) {
                $current = $snapshots[$tier->value] ?? null;
                if (!$this->isStale($tier, $current, $states, $now)
                    || ($tier === Tier::Fast && !$this->mayComputeFast($current, $now))
                ) {
                    continue;
                }
                if ($computed && $budget->exhausted()) {
                    break;
                }

                $single = $this->locks->tryAcquire('s' . $idSite . 'v' . $viewId . $tier->value, $this->lockTtl());
                if ($single === null) {
                    continue; // another request computes this tier right now
                }
                try {
                    $slot = $slot ?? $this->gateSlot();
                    if ($slot === null) {
                        break; // the instance computes enough already: serve what is stored
                    }
                    $ctx = new ComputeContext(
                        $idSite, $viewId, $now, $timezone, $states->level(), $this->limits, $budget, $visitorLogEnabled, $current?->payload ?? []
                    );
                    [$snapshots[$tier->value], $state, $timedOut, $seconds] = $this->compute($tier, $ctx, $current, $states->get($tier));
                    $computedMs[$tier->value] = (int) round($seconds * 1000);
                    $states = $states->with($tier, $state);
                    $computed = true;
                } finally {
                    $single->release();
                }
                if ($timedOut) {
                    break; // a struggling database gets no further reads from this poll
                }
            }
        } finally {
            if ($slot !== null) {
                $slot->release();
            }
        }
        if ($computed) {
            $this->store->writeState($idSite, $viewId, $states, $now);
        }

        return $this->result($snapshots, $states, $now, $since, $computedMs);
    }

    private function isStale(Tier $tier, ?Snapshot $snapshot, DegradeStates $states, int $now): bool
    {
        if (!Schedule::computes($tier, $states)) {
            return false;
        }
        return $snapshot === null || $snapshot->computedAt + Schedule::interval($tier, $states, $this->limits) <= $now;
    }

    /** Beyond the cap of active keys, a key that is not active yet gets only the slow and today tiers. */
    private function mayComputeFast(?Snapshot $fast, int $now): bool
    {
        if ($fast !== null && $fast->computedAt >= $now - self::ACTIVE_SECONDS) {
            return true;
        }
        return $this->store->countActiveKeys($now - self::ACTIVE_SECONDS) < $this->limits->maxActiveKeys;
    }

    private function gateSlot(): ?LockHandle
    {
        for ($slot = 1; $slot <= $this->limits->maxConcurrent; $slot++) {
            $handle = $this->locks->tryAcquire('gate' . $slot, $this->lockTtl());
            if ($handle !== null) {
                return $handle;
            }
        }
        return null;
    }

    /** Long enough for one poll's computations at the budget; a crashed request frees the key after it. */
    private function lockTtl(): int
    {
        return $this->limits->budgetSeconds + self::LOCK_MARGIN_SECONDS;
    }

    /**
     * @return array{0: Snapshot, 1: DegradeState, 2: bool, 3: float} the new snapshot, the tier's new degrade state, timed
     *     out, seconds taken
     */
    private function compute(Tier $tier, ComputeContext $ctx, ?Snapshot $current, DegradeState $state): array
    {
        $start = $this->clock->microtime();
        $old = $current?->payload ?? [];
        try {
            $result = $this->computer->compute($tier, $ctx);
            // only a partial result keeps the keys it could not compute; a complete one (also level 3, counters only)
            // replaces the payload, so lists of an earlier run never look current
            $payload = $result->partial ? array_replace($old, $result->payload) : $result->payload;
            // how long the calculation took, for the board's technical info (Peter, 2026-11-19)
            $ms = (int) round(($this->clock->microtime() - $start) * 1000);
            $snapshot = new Snapshot(
                $ctx->idSite, $ctx->viewId, $tier, $ctx->now, $result->dataUntil, array_replace($payload, ['partial' => $result->partial, 'ms' => $ms])
            );
            $outcome = new RunOutcome($tier, $this->clock->microtime() - $start, false, $result->capHit);
        } catch (QueryTimeout $e) {
            // the attempt counts as a computation (no retry at every poll); the data keeps its age
            $snapshot = new Snapshot($ctx->idSite, $ctx->viewId, $tier, $ctx->now, $current?->dataUntil ?? 0, $old);
            $outcome = new RunOutcome($tier, $this->clock->microtime() - $start, true, false);
        }

        $this->store->write($snapshot);
        return [$snapshot, DegradeController::after($state, $outcome, $this->limits), $outcome->timedOut, $outcome->seconds];
    }

    /**
     * @param array<string, Snapshot> $snapshots
     */
    private function result(array $snapshots, DegradeStates $states, int $now, string $since, array $computedMs): PollResult
    {
        $payload = [];
        $ages = [];
        $stamps = [];
        $interval = null;
        foreach (Tier::ordered() as $tier) {
            $snapshot = $snapshots[$tier->value] ?? null;
            $payload[$tier->value] = $snapshot?->payload ?? [];
            $ages[$tier->value] = $snapshot !== null && $snapshot->dataUntil > 0 ? max(0, $now - $snapshot->dataUntil) : null;
            $stamps[] = $snapshot?->computedAt ?? 0;
            if (Schedule::computes($tier, $states)) {
                $seconds = Schedule::interval($tier, $states, $this->limits);
                $interval = $interval === null ? $seconds : min($interval, $seconds);
            }
        }

        $level = $states->level();
        $version = sha1(implode(',', $stamps) . '|' . $level);
        $random = max(0.0, min(1.0, (float) ($this->random)()));
        $nextPoll = (int) round(($interval ?? $this->limits->fastSeconds) * (1 + self::MAX_JITTER * $random));
        $notModified = $since !== '' && hash_equals($version, $since);
        $lag = (int) ($payload['fast']['lag'] ?? 0);

        return new PollResult($notModified ? [] : $payload, $ages, $level, $lag, $version, $nextPoll, $notModified, $computedMs);
    }
}
