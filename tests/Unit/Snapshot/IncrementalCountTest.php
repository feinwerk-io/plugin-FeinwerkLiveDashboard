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
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\IncrementalCount;

/**
 * Today's hits and conversions counted from a settled base plus the minutes since (load test 2026-11-19): the reads
 * a run makes, by their bound times, and the totals it gives.
 *
 * @group FeinwerkLiveDashboard
 */
class IncrementalCountTest extends TestCase
{
    private const DAY = 1773097200; // 2026-03-09 23:00 UTC, midnight in Berlin
    private const NOW = self::DAY + 13 * 3600; // 12:00 UTC

    /** @var array<int, array{0: int, 1: int}> */
    private array $reads = [];
    /** @var array<int, int> one row per hit time */
    private array $hits = [];
    /** @var int|null a read starting at this time hits the statement limit */
    private ?int $slowFrom = null;

    protected function setUp(): void
    {
        $this->reads = [];
        $this->hits = [];
        $this->slowFrom = null;
    }

    private function counter(): callable
    {
        return function (int $from, int $to): array {
            $this->reads[] = [$from, $to];
            if ($this->slowFrom !== null && $from === $this->slowFrom) {
                throw new QueryTimeout('slow');
            }
            $n = count(array_filter($this->hits, static fn (int $t): bool => $t >= $from && $t < $to));
            return ['n' => $n, 'revenue' => 2.5 * $n];
        };
    }

    private function runOnce(?array $state, int $now = self::NOW, int $lag = 0): array
    {
        return IncrementalCount::run($this->counter(), ['n' => 0, 'revenue' => 0.0], $state, self::DAY, $now, $lag);
    }

    private static function state(int $until, int $full, int $n, float $revenue = 0.0, int $day = self::DAY): array
    {
        return ['day' => $day, 'until' => $until, 'full' => $full, 'sums' => ['n' => $n, 'revenue' => $revenue]];
    }

    public function test_firstRun_countsTheDayInTwoPartsAndKeepsTheSettledOne(): void
    {
        $this->hits = [self::DAY + 60, self::NOW - 3600, self::NOW - 60];

        [$totals, $state] = $this->runOnce(null);

        $settled = self::NOW - IncrementalCount::SETTLE_SECONDS;
        $this->assertSame([[self::DAY, $settled], [$settled, self::NOW]], $this->reads);
        $this->assertSame(['n' => 3, 'revenue' => 7.5], $totals);
        $this->assertSame(self::state($settled, self::NOW, 2, 5.0), $state);
    }

    public function test_nextRun_readsOnlyFromTheSettledTime(): void
    {
        $until = self::NOW - 300 - IncrementalCount::SETTLE_SECONDS;
        // a deliberately wrong base: the totals show that nothing before $until was read again
        [$totals, $state] = $this->runOnce(self::state($until, self::NOW - 600, 1000, 100.0));

        $settled = self::NOW - IncrementalCount::SETTLE_SECONDS;
        $this->assertSame([[$until, $settled], [$settled, self::NOW]], $this->reads);
        $this->assertSame(['n' => 1000, 'revenue' => 100.0], $totals);
        $this->assertSame(self::state($settled, self::NOW - 600, 1000, 100.0), $state);
    }

    public function test_aLateHitWithinTheSettleTime_isCountedInTheNextRun(): void
    {
        $first = self::NOW - 300;
        [, $state] = $this->runOnce(null, $first);
        // tracked after the first run, two minutes back-dated (queued tracking)
        $this->hits[] = $first - 120;

        [$totals] = $this->runOnce($state);

        $this->assertSame(1, $totals['n']);
    }

    public function test_aReaderThatLags_settlesLater(): void
    {
        $this->runOnce(null, self::NOW, 1200);

        $this->assertSame(self::NOW - 1200 - IncrementalCount::LAG_MARGIN_SECONDS, $this->reads[0][1]);
    }

    public function test_anHourAfterTheLastFullCount_countsTheDayAgain(): void
    {
        $this->hits = [self::DAY + 60, self::NOW - 60];
        $until = self::NOW - 300 - IncrementalCount::SETTLE_SECONDS;

        [$totals, $state] = $this->runOnce(self::state($until, self::NOW - IncrementalCount::RECOUNT_SECONDS, 1000));

        $this->assertSame(self::DAY, $this->reads[0][0], 'from midnight again');
        $this->assertSame(2, $totals['n'], 'the recount replaces the base');
        $this->assertSame(self::NOW, $state['full']);
    }

    public function test_aRecountThatHitsTheLimit_goesOnFromTheBaseAndRetriesInAnHour(): void
    {
        $this->hits = [self::NOW - 60];
        $this->slowFrom = self::DAY;
        $until = self::NOW - 300 - IncrementalCount::SETTLE_SECONDS;

        [$totals, $state] = $this->runOnce(self::state($until, self::NOW - IncrementalCount::RECOUNT_SECONDS, 1000));

        $this->assertSame(1001, $totals['n']);
        $this->assertSame(self::NOW, $state['full'], 'the attempt counts: no retry at every run');
        $this->assertSame(1000, $state['sums']['n']);

        $this->reads = [];
        $this->runOnce($state, self::NOW + 300);
        $this->assertNotSame(self::DAY, $this->reads[0][0], 'the next run reads the new minutes only');
    }

    public function test_aTimeoutWithoutABase_endsTheRun(): void
    {
        $this->slowFrom = self::DAY;

        $this->expectException(QueryTimeout::class);
        $this->runOnce(null);
    }

    public function test_aNewDay_startsFromItsMidnight(): void
    {
        $this->hits = [self::DAY + 60];

        [$totals, $state] = $this->runOnce(self::state(self::DAY - 600, self::NOW - 600, 1000, 0.0, self::DAY - 86400));

        $this->assertSame(self::DAY, $this->reads[0][0]);
        $this->assertSame(1, $totals['n']);
        $this->assertSame(self::DAY, $state['day']);
    }

    public function test_justAfterMidnight_countsNothingOfYesterday(): void
    {
        $this->hits = [self::DAY - 60, self::DAY + 60];

        [$totals, $state] = $this->runOnce(null, self::DAY + 180);

        $this->assertSame([[self::DAY, self::DAY + 180]], $this->reads, 'no settled part yet: one read from midnight');
        $this->assertSame(1, $totals['n']);
        $this->assertSame(self::DAY, $state['until']);
    }

    public function test_aStateThatDoesNotFit_isCountedAgain(): void
    {
        foreach ([['day' => self::DAY], self::state(self::NOW + 60, self::NOW, 5), self::state(self::DAY - 1, self::NOW, 5), 'x'] as $broken) {
            $this->reads = [];
            $this->runOnce(is_array($broken) ? $broken : null);
            $this->assertSame(self::DAY, $this->reads[0][0]);
        }
    }
}
