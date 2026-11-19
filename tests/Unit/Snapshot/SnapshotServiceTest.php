<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Snapshot;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Data\QueryTimeout;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeState;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\DegradeStates;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Limits;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\ComputeContext;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\Snapshot;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\SnapshotService;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\TierResult;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes\FakeTierComputer;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes\FixedClock;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes\InMemoryLocks;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes\InMemorySnapshotStore;

/**
 * @group FeinwerkLiveDashboard
 */
class SnapshotServiceTest extends TestCase
{
    private const NOW = 1795000000;

    private FixedClock $clock;
    private InMemorySnapshotStore $store;
    private InMemoryLocks $locks;
    private FakeTierComputer $computer;
    private float $random = 0.0;

    protected function setUp(): void
    {
        $this->clock = new FixedClock(self::NOW);
        $this->store = new InMemorySnapshotStore();
        $this->locks = new InMemoryLocks();
        $this->computer = new FakeTierComputer($this->clock);
    }

    private function service(?Limits $limits = null): SnapshotService
    {
        return new SnapshotService($this->store, $this->locks, $this->computer, $this->clock, $limits ?? new Limits(), fn (): float => $this->random);
    }

    private function poll(int $idSite = 1, string $since = '', ?Limits $limits = null)
    {
        return $this->service($limits)->poll($idSite, 0, 'Europe/Berlin', true, $since);
    }

    private function put(Tier $tier, int $age, array $payload, int $idSite = 1, ?int $dataAge = null): void
    {
        $this->store->write(new Snapshot($idSite, 0, $tier, self::NOW - $age, self::NOW - ($dataAge ?? $age), $payload));
    }

    private function putAllFresh(): void
    {
        $this->put(Tier::Fast, 5, ['activeNow' => 3]);
        $this->put(Tier::Slow, 30, ['visits30' => 40]);
        $this->put(Tier::Today, 100, ['visits' => 400]);
    }

    public function test_freshSnapshots_computeNothing(): void
    {
        $this->putAllFresh();
        $writes = $this->store->writes;

        $result = $this->poll();

        $this->assertSame([], $this->computer->computedTiers());
        $this->assertSame($writes, $this->store->writes);
        $this->assertSame(['activeNow' => 3], $result->payload['fast']);
        $this->assertSame(['visits30' => 40], $result->payload['slow']);
        $this->assertSame(['visits' => 400], $result->payload['today']);
        $this->assertSame(['fast' => 5, 'slow' => 30, 'today' => 100], $result->ages);
        $this->assertSame(0, $result->level);
        $this->assertFalse($result->notModified);
    }

    public function test_aTierComputesWithItsStoredPayload(): void
    {
        $this->put(Tier::Fast, 5, ['activeNow' => 3]);
        $this->put(Tier::Slow, 30, ['visits30' => 40]);
        $this->put(Tier::Today, 400, ['visits' => 400, 'counted' => ['actions' => ['until' => 1]]]);

        $this->poll();

        $this->assertSame(['today'], $this->computer->computedTiers());
        $this->assertSame(['visits' => 400, 'counted' => ['actions' => ['until' => 1]]], $this->computer->calls[0]['ctx']->previous);
    }

    public function test_staleFast_computesOnceAndStores(): void
    {
        $this->computer->results['fast'] = new TierResult(['activeNow' => 7], self::NOW, false);

        $result = $this->poll();

        $this->assertSame(['fast', 'slow', 'today'], $this->computer->computedTiers(), 'every stale tier, one gate slot');
        $this->assertSame(['activeNow' => 7, 'partial' => false, 'ms' => 10], $result->payload['fast']);
        $this->assertSame(['fast' => 0, 'slow' => 0, 'today' => 0], $result->ages);
        $stored = $this->store->readAll(1, 0)['snapshots']['fast'];
        $this->assertSame(self::NOW, $stored->computedAt);
        $this->assertSame([], $this->locks->held, 'locks released');
        $this->assertSame(['s1v0fast', 'gate1', 's1v0slow', 's1v0today'], $this->locks->acquired);
    }

    public function test_locks_lastTheBudgetPlusAMargin(): void
    {
        $this->computer->during = function (Tier $tier): void {
            // one gate slot for the whole poll, the single-flight key of the tier being computed
            $held = $this->locks->held;
            ksort($held);
            $expected = ['gate1' => 18, 's1v0' . $tier->value => 18];
            ksort($expected);
            $this->assertSame($expected, $held);
        };
        $this->poll();
        $this->assertCount(3, $this->computer->calls);
    }

    public function test_result_namesTheComputedTiersWithTheirMilliseconds(): void
    {
        $this->computer->seconds = ['fast' => 0.2, 'slow' => 0.5, 'today' => 0.01];

        $this->assertSame(['fast' => 200, 'slow' => 500, 'today' => 10], $this->poll()->computedMs);

        $this->clock->advance(1);
        $this->assertSame([], $this->poll()->computedMs, 'nothing computed');
    }

    public function test_aSecondPoll_findsEverythingFresh(): void
    {
        $this->poll();
        $this->clock->advance(1);
        $this->poll();

        $this->assertSame(['fast', 'slow', 'today'], $this->computer->computedTiers());
    }

    public function test_sharedBudget_stopsBeforeTheNextTier(): void
    {
        $this->computer->seconds['fast'] = 9.0;

        $this->poll();

        $this->assertSame(['fast'], $this->computer->computedTiers());
    }

    public function test_timeout_endsThePoll(): void
    {
        $this->computer->results['fast'] = new QueryTimeout('timeout');

        $this->poll();

        $this->assertSame(['fast'], $this->computer->computedTiers(), 'a struggling database gets no further reads');
    }

    /**
     * One viewer who follows nextPollSeconds for ten minutes: the slow and today tiers must keep up (they starved when a
     * poll computed only the first stale tier).
     */
    public function test_singleViewer_keepsEveryTierFresh(): void
    {
        $polls = 0;
        $end = self::NOW + 600;
        while ($this->clock->now() < $end) {
            $result = $this->poll();
            $polls++;
            $this->clock->advance($result->nextPollSeconds);
        }

        $counts = array_count_values($this->computer->computedTiers());
        $this->assertSame(60, $polls);
        $this->assertSame(['fast' => 60, 'slow' => 10, 'today' => 2], $counts);
    }

    public function test_slowCapHitEveryRun_keepsLevel1WithoutFlapping(): void
    {
        $this->computer->results['slow'] = new TierResult(['visits30' => 1], self::NOW, true);
        $levels = [];
        $end = self::NOW + 600;
        while ($this->clock->now() < $end) {
            $result = $this->poll();
            $levels[] = $result->level;
            $this->clock->advance($result->nextPollSeconds);
        }

        $this->assertSame([1], array_values(array_unique($levels)), 'clean fast runs do not lower what the slow tier raised');
        $slowLevels = array_map(static fn (array $call): int => $call['ctx']->level, array_values(array_filter(
            $this->computer->calls,
            static fn (array $call): bool => $call['tier'] === Tier::Slow
        )));
        $this->assertSame(0, $slowLevels[0]);
        $this->assertSame([1], array_values(array_unique(array_slice($slowLevels, 1))), 'later slow runs read with the halved cap');
    }

    public function test_oneTodayTimeout_recoversToLevel0(): void
    {
        // a tier that raised the level itself keeps running (otherwise level 2 would keep "today" waiting forever)
        $timeouts = 1;
        $this->computer->during = function (Tier $tier) use (&$timeouts): void {
            $this->computer->results['today'] = $tier === Tier::Today && $timeouts-- > 0
                ? new QueryTimeout('timeout')
                : new TierResult(['visits' => 1], self::NOW, false);
        };
        $end = self::NOW + 3600;
        $todayRuns = 0;
        $level = null;
        while ($this->clock->now() < $end) {
            $result = $this->poll();
            $level = $result->level;
            $this->clock->advance($result->nextPollSeconds);
        }
        $todayRuns = count(array_filter($this->computer->computedTiers(), static fn (string $t): bool => $t === 'today'));

        $this->assertGreaterThanOrEqual(11, $todayRuns, 'the timeout and then every 5 minutes');
        $this->assertSame(0, $level);
    }

    public function test_slowTimeoutsEveryRun_reachLevel3AndRetry(): void
    {
        $this->computer->results['slow'] = new QueryTimeout('timeout');
        $end = self::NOW + 1200;
        $maxLevel = 0;
        while ($this->clock->now() < $end) {
            $result = $this->poll();
            $maxLevel = max($maxLevel, $result->level);
            $this->clock->advance($result->nextPollSeconds);
        }

        $this->assertSame(3, $maxLevel, 'fast runs between them do not reset the slow timeouts');
        $state = $this->store->readAll(1, 0)['states']->get(Tier::Slow);
        $this->assertSame(3, $state->level);
        $this->assertGreaterThanOrEqual(4, $state->timeoutsInARow, 'the slow tier keeps retrying after its backoff');
        $fastAtLevel3 = array_filter($this->computer->calls, static fn (array $call): bool => $call['tier'] === Tier::Fast && $call['ctx']->level === 3);
        $this->assertNotEmpty($fastAtLevel3, 'meanwhile the fast tier computes counters only');
    }

    public function test_staleSlow_isComputedWhenFastIsFresh(): void
    {
        $this->putAllFresh();
        $this->put(Tier::Slow, 60, ['visits30' => 40]);

        $this->poll();

        $this->assertSame(['slow'], $this->computer->computedTiers());
    }

    public function test_lockHeld_servesStaleWithoutComputing(): void
    {
        $this->putAllFresh();
        $this->put(Tier::Fast, 20, ['activeNow' => 3]);
        $this->locks->hold('s1v0fast');

        $result = $this->poll();

        $this->assertSame([], $this->computer->computedTiers());
        $this->assertSame(['activeNow' => 3], $result->payload['fast']);
        $this->assertSame(20, $result->ages['fast']);
    }

    public function test_lockHeldOnFast_computesTheNextStaleTier(): void
    {
        $this->put(Tier::Fast, 20, ['activeNow' => 3]);
        $this->locks->hold('s1v0fast');

        $this->poll();

        $this->assertSame(['slow', 'today'], $this->computer->computedTiers());
    }

    public function test_gateFull_servesStale(): void
    {
        $this->put(Tier::Fast, 20, ['activeNow' => 3]);
        $this->locks->hold('gate1');
        $this->locks->hold('gate2');

        $result = $this->poll();

        $this->assertSame([], $this->computer->computedTiers());
        $this->assertSame(['activeNow' => 3], $result->payload['fast']);
        $this->assertArrayNotHasKey('s1v0fast', $this->locks->held);
    }

    public function test_secondGateSlot_isUsedWhenTheFirstIsTaken(): void
    {
        $this->locks->hold('gate1');
        $this->poll();
        $this->assertSame(['s1v0fast', 'gate2', 's1v0slow', 's1v0today'], $this->locks->acquired);
    }

    public function test_timeout_keepsOldPayloadAndRaisesLevel(): void
    {
        $this->putAllFresh();
        $this->put(Tier::Fast, 20, ['activeNow' => 3]);
        $this->computer->results['fast'] = new QueryTimeout('timeout');

        $result = $this->poll();

        $this->assertSame(['activeNow' => 3], $result->payload['fast']);
        $this->assertSame(2, $result->level);
        $this->assertSame(20, $result->ages['fast'], 'the data keeps its age');
        $this->assertSame(1, $this->store->readAll(1, 0)['states']->get(Tier::Fast)->timeoutsInARow);

        // the attempt counts as a computation, so the next polls do not hammer the database
        $this->clock->advance(1);
        $this->poll();
        $this->assertSame(['fast'], $this->computer->computedTiers());
    }

    public function test_timeoutWithoutOldSnapshot_givesAnEmptyTier(): void
    {
        $this->computer->results['fast'] = new QueryTimeout('timeout');

        $result = $this->poll();

        $this->assertSame([], $result->payload['fast']);
        $this->assertNull($result->ages['fast']);
    }

    public function test_aComputedTier_keepsHowLongItTook(): void
    {
        // shown on the board when the technical info is on (Peter, 2026-11-19)
        $this->computer->seconds['fast'] = 0.0484;
        $this->computer->seconds['slow'] = 0.8456;

        $result = $this->poll();

        $this->assertSame(48, $result->payload['fast']['ms']);
        $this->assertSame(846, $result->payload['slow']['ms']);
        $this->assertSame(846, $this->store->readAll(1, 0)['snapshots']['slow']->payload['ms'], 'stored with the snapshot');
    }

    public function test_partialResult_keepsMissingKeys(): void
    {
        $this->put(Tier::Fast, 20, ['activeNow' => 3, 'visits5' => 9, 'partial' => false]);
        $this->computer->results['fast'] = new TierResult(['activeNow' => 7], self::NOW, false, true);

        $result = $this->poll();

        $this->assertSame(['activeNow' => 7, 'visits5' => 9, 'partial' => true, 'ms' => 10], $result->payload['fast']);
    }

    public function test_completeResult_replacesThePayload(): void
    {
        // level 3 computes counters only: lists of an earlier run must not look current
        $this->put(Tier::Fast, 20, ['activeNow' => 3, 'pagesNow' => ['urls' => [[5, 'a', 1]], 'titles' => []]]);
        $this->computer->results['fast'] = new TierResult(['activeNow' => 4], self::NOW, false);

        $result = $this->poll();

        $this->assertSame(['activeNow' => 4, 'partial' => false, 'ms' => 10], $result->payload['fast']);
    }

    public function test_completeResult_clearsAnEarlierPartialFlag(): void
    {
        $this->put(Tier::Fast, 20, ['activeNow' => 3, 'partial' => true]);
        $result = $this->poll();
        $this->assertFalse($result->payload['fast']['partial']);
    }

    public function test_undecodableRow_isRecomputed(): void
    {
        $this->putAllFresh();
        $this->store->putRaw(1, 0, 'fast', self::NOW, 'garbage');

        $this->poll();

        $this->assertSame(['fast'], $this->computer->computedTiers());
    }

    public function test_activeKeyCap_skipsFastForNewKey(): void
    {
        $this->put(Tier::Fast, 10, ['activeNow' => 1], 2);

        $this->poll(1, '', new Limits(maxActiveKeys: 1));

        $this->assertSame(['slow', 'today'], $this->computer->computedTiers());
    }

    public function test_activeKey_keepsItsFastTierUnderTheCap(): void
    {
        $this->put(Tier::Fast, 10, ['activeNow' => 1], 2);
        $this->put(Tier::Fast, 200, ['activeNow' => 1], 1);

        $this->poll(1, '', new Limits(maxActiveKeys: 1));

        $this->assertSame(['fast', 'slow', 'today'], $this->computer->computedTiers());
    }

    public function test_sameVersion_isNotModified(): void
    {
        $this->putAllFresh();
        $first = $this->poll();

        $second = $this->poll(1, $first->version);

        $this->assertTrue($second->notModified);
        $this->assertSame([], $second->payload);
        $this->assertSame($first->version, $second->version);
    }

    public function test_newComputation_changesTheVersion(): void
    {
        $this->putAllFresh();
        $first = $this->poll();
        $this->clock->advance(10);

        $second = $this->poll(1, $first->version);

        $this->assertFalse($second->notModified);
        $this->assertNotSame($first->version, $second->version);
    }

    public function test_lag_comesFromTheFastSnapshotAlsoWhenNotModified(): void
    {
        $this->putAllFresh();
        $this->put(Tier::Fast, 5, ['activeNow' => 3, 'lag' => 45]);
        $first = $this->poll();
        $this->assertSame(45, $first->lag);
        $this->assertSame(45, $this->poll(1, $first->version)->lag);
    }

    public function test_nextPoll_hasJitterWithin20Percent(): void
    {
        $this->putAllFresh();
        $this->random = 0.0;
        $this->assertSame(10, $this->poll()->nextPollSeconds);
        $this->random = 1.0;
        $this->assertSame(12, $this->poll()->nextPollSeconds);
    }

    public function test_nextPoll_followsTheLevel(): void
    {
        $this->putAllFresh();
        $this->store->writeState(1, 0, (new DegradeStates())->with(Tier::Fast, new DegradeState(2)), self::NOW);
        $this->assertSame(30, $this->poll()->nextPollSeconds);

        $this->store->writeState(1, 0, (new DegradeStates())->with(Tier::Fast, new DegradeState(3, 3, 0, 120)), self::NOW);
        $this->assertSame(120, $this->poll()->nextPollSeconds);
    }

    public function test_level3_computesOnlyTheFastTier(): void
    {
        $this->store->writeState(1, 0, (new DegradeStates())->with(Tier::Fast, new DegradeState(3, 3, 0, 60)), self::NOW);

        $this->poll();
        $this->clock->advance(61);
        $this->poll();

        $this->assertSame(['fast', 'fast'], $this->computer->computedTiers());
    }

    public function test_locksReleasedAfterException(): void
    {
        $this->computer->results['fast'] = new \RuntimeException('boom');

        try {
            $this->poll();
            $this->fail('exception expected');
        } catch (\RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }
        $this->assertSame([], $this->locks->held);
    }

    public function test_context_carriesSiteViewAndLevel(): void
    {
        $this->store->writeState(1, 0, (new DegradeStates())->with(Tier::Slow, new DegradeState(1)), self::NOW);

        $this->service()->poll(1, 0, 'UTC+5.5', false, '');

        /** @var ComputeContext $ctx */
        $ctx = $this->computer->calls[0]['ctx'];
        $this->assertSame(1, $ctx->idSite);
        $this->assertSame(0, $ctx->viewId);
        $this->assertSame(self::NOW, $ctx->now);
        $this->assertSame('UTC+5.5', $ctx->timezone);
        $this->assertFalse($ctx->visitorLogEnabled);
        $this->assertSame(1, $ctx->level, 'the effective level: the highest of the tiers');
        $this->assertSame(8, $ctx->limits->budgetSeconds);
        $this->assertSame($ctx->budget, $this->computer->calls[2]['ctx']->budget, 'one budget for every tier of the poll');
        $this->assertEqualsWithDelta(7.97, $ctx->budget->remaining(), 0.001, 'three tiers of 0.01 s each');
    }

    public function test_slowRun_raisesTheLevel(): void
    {
        $this->computer->seconds['fast'] = 1.5;
        $this->assertSame(2, $this->poll()->level);
    }
}
