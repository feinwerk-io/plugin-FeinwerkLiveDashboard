<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

/**
 * The degrade state of each tier of one website and view. Only a tier's own runs move its state, so frequent clean fast
 * runs never lower a level the slow tier raised, and never reset the slow tier's timeouts. The effective level, the
 * highest of them, slows every tier down (Schedule).
 */
final class DegradeStates
{
    /** @param array<string, DegradeState> $byTier */
    public function __construct(private readonly array $byTier = [])
    {
    }

    public function get(Tier $tier): DegradeState
    {
        return $this->byTier[$tier->value] ?? new DegradeState();
    }

    public function with(Tier $tier, DegradeState $state): self
    {
        $byTier = $this->byTier;
        $byTier[$tier->value] = $state;
        return new self($byTier);
    }

    /**
     * The board's level: the highest of the fast and slow tiers. The today tier is left out: its read grows all day,
     * and its timeouts slow only the today counters down (their own level and backoff), never the lists, the map or
     * the live log.
     */
    public function level(): int
    {
        $level = 0;
        foreach ($this->byTier as $tier => $state) {
            if ($tier !== Tier::Today->value) {
                $level = max($level, $state->level);
            }
        }
        return $level;
    }

    public function toArray(): array
    {
        return array_map(static fn (DegradeState $state): array => $state->toArray(), $this->byTier);
    }

    /** Broken data or an unknown layout counts as level 0 for every tier. */
    public static function fromArray(?array $data): self
    {
        $byTier = [];
        foreach (Tier::ordered() as $tier) {
            if (isset($data[$tier->value]) && is_array($data[$tier->value])) {
                $byTier[$tier->value] = DegradeState::fromArray($data[$tier->value]);
            }
        }
        return new self($byTier);
    }
}
