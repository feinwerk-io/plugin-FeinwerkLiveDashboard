<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Snapshot;

/**
 * What a poll returns: the payload of each tier (empty when not modified), the age of each tier's data in seconds (null
 * when there is none yet), the effective degrade level, the measured reader lag, a version for "not modified" answers
 * and the server's next poll interval.
 */
final class PollResult
{
    public function __construct(
        public readonly array $payload,
        public readonly array $ages,
        public readonly int $level,
        public readonly int $lag,
        public readonly string $version,
        public readonly int $nextPollSeconds,
        public readonly bool $notModified,
        /** tiers computed by this poll => wall time in milliseconds (diagnostics, load test) */
        public readonly array $computedMs = []
    ) {
    }
}
