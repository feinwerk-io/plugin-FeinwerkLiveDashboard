<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

/**
 * Degrade level of one website and view (concept 3.8), stored next to its snapshots.
 */
final class DegradeState
{
    public const MAX_LEVEL = 3;

    public function __construct(
        public readonly int $level = 0,
        public readonly int $timeoutsInARow = 0,
        public readonly int $goodRuns = 0,
        public readonly int $backoffSeconds = 0
    ) {
    }

    public function toArray(): array
    {
        return [
            'level' => $this->level,
            'timeoutsInARow' => $this->timeoutsInARow,
            'goodRuns' => $this->goodRuns,
            'backoffSeconds' => $this->backoffSeconds,
        ];
    }

    /** A missing or broken state counts as level 0, so a lost row never blocks a website. */
    public static function fromArray(?array $data): self
    {
        if ($data === null || !is_int($data['level'] ?? null)) {
            return new self();
        }
        $int = static fn ($v): int => is_int($v) ? max(0, $v) : 0;
        return new self(
            max(0, min(self::MAX_LEVEL, $data['level'])),
            $int($data['timeoutsInARow'] ?? 0),
            $int($data['goodRuns'] ?? 0),
            $int($data['backoffSeconds'] ?? 0)
        );
    }
}
