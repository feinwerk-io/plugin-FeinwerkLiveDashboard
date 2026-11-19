<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Snapshot;

use Piwik\Plugins\FeinwerkLiveDashboard\Data\QueryTimeout;

/**
 * A count since local midnight that does not read the whole day at every run (load test 2026-11-19: the today counters
 * were the one read that grows with a website's size). Rows older than the settle time count as complete: their sums
 * are kept as the base, and each run reads only the rows from the base's end, so the base grows by the minutes since the
 * last run, plus the recent minutes fresh (late rows of queued tracking or a lagging reader database still arrive
 * there). Once an hour the base is counted again from midnight, which also takes in rows that came later than the
 * settle time or were deleted. A recount that hits the statement limit keeps the base and is tried again an hour later.
 *
 * Only for rows whose time never changes (hits, conversions): visits move their last action time forward.
 */
final class IncrementalCount
{
    /** rows at least this old count as complete */
    public const SETTLE_SECONDS = 900;
    /** with a lagging reader database, rows settle this long after the lag */
    public const LAG_MARGIN_SECONDS = 300;
    /** the base is counted again from midnight after this time */
    public const RECOUNT_SECONDS = 3600;

    /**
     * @param callable $count fn(int $from, int $to): array<string, int|float>, the sums of the rows in [from, to) (UTC
     *                        timestamps); may throw QueryTimeout
     * @param array<string, int|float> $zero the sums of no rows
     * @param array|null $state the state of the last run (from its snapshot), or null
     * @return array{0: array<string, int|float>, 1: array{day: int, until: int, full: int, sums: array<string, int|float>}}
     *     the totals since midnight and the state to keep
     * @throws QueryTimeout when a read hits the statement limit and there is no base to go on from
     */
    public static function run(callable $count, array $zero, ?array $state, int $dayStart, int $now, int $lag): array
    {
        $settled = max($dayStart, min($now, $now - max(self::SETTLE_SECONDS, $lag + self::LAG_MARGIN_SECONDS)));
        $state = self::valid($state, $zero, $dayStart, $now) ? $state : null;
        if ($state !== null) {
            $settled = max($settled, $state['until']);
        }
        $read = static fn (int $from, int $to): array => $from < $to ? $count($from, $to) : $zero;

        $base = null;
        $full = $state['full'] ?? $now;
        if ($state === null || $now - $state['full'] >= self::RECOUNT_SECONDS) {
            try {
                $base = $read($dayStart, $settled);
            } catch (QueryTimeout $e) {
                if ($state === null) {
                    throw $e;
                }
                // the attempt counts: the next recount comes in an hour, the runs until then go on from the base
            }
            $full = $now;
        }
        if ($base === null) {
            $base = self::add($state['sums'], $read($state['until'], $settled));
        }

        $state = ['day' => $dayStart, 'until' => $settled, 'full' => $full, 'sums' => $base];
        return [self::add($base, $read($settled, $now)), $state];
    }

    private static function valid(?array $state, array $zero, int $dayStart, int $now): bool
    {
        if ($state === null || ($state['day'] ?? null) !== $dayStart || !is_int($state['full'] ?? null)) {
            return false;
        }
        $until = $state['until'] ?? null;
        if (!is_int($until) || $until < $dayStart || $until > $now || !is_array($state['sums'] ?? null)) {
            return false;
        }
        foreach ($zero as $key => $value) {
            if (!is_int($state['sums'][$key] ?? null) && !is_float($state['sums'][$key] ?? null)) {
                return false;
            }
        }
        return true;
    }

    /** @return array<string, int|float> */
    private static function add(array $a, array $b): array
    {
        foreach ($b as $key => $value) {
            $a[$key] = ($a[$key] ?? 0) + $value;
        }
        return $a;
    }
}
