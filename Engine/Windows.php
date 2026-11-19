<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

/**
 * The time windows of one computation (concept 2.2 and 3.6); the slow tier reads the widest list window. With a measured reader lag above 30 seconds the windows end
 * at the newest data instead of the wall clock, so panels do not look empty; above 15 minutes the data is stale and the
 * board shows a notice instead of misleading numbers.
 */
final class Windows
{
    public const DELAY_SECONDS = 30;
    public const STALE_SECONDS = 900;
    /** the time windows of the lists and key numbers a viewer can choose (Peter, 2026-11-18: "3, 10, 30, 60 minuten") */
    public const LIST_MINUTES = [3, 10, 30, 60];

    private function __construct(
        public readonly int $end,
        public readonly int $activeFrom,
        public readonly int $fastFrom,
        public readonly int $fiveFrom,
        public readonly int $slowFrom,
        public readonly bool $delayed,
        public readonly bool $stale
    ) {
    }

    public static function compute(int $now, int $readerLagSeconds, Limits $limits): self
    {
        $delayed = $readerLagSeconds > self::DELAY_SECONDS;
        $end = $delayed ? $now - $readerLagSeconds : $now;

        return new self(
            $end,
            $end - $limits->activeMinutes * 60,
            $end - max(5, $limits->activeMinutes) * 60,
            $end - 300,
            $end - max(self::LIST_MINUTES) * 60,
            $delayed,
            $readerLagSeconds > self::STALE_SECONDS
        );
    }

    /** @return array<string, string> list window in minutes => its first time ('Y-m-d H:i:s', UTC) */
    public function listFroms(): array
    {
        $froms = [];
        foreach (self::LIST_MINUTES as $minutes) {
            $froms[(string) $minutes] = self::utc($this->end - $minutes * 60);
        }
        return $froms;
    }

    /** A timestamp as bound in SQL against the UTC datetime columns of the log tables. */
    public static function utc(int $timestamp): string
    {
        return gmdate('Y-m-d H:i:s', $timestamp);
    }
}
