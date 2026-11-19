<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes;

use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\ComputeContext;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\TierComputer;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\TierResult;

/**
 * Returns a scripted result per tier (or throws one), takes a scripted time on a FixedClock and records its calls.
 */
final class FakeTierComputer implements TierComputer
{
    /** @var array<string, TierResult|\Throwable> */
    public array $results = [];
    /** @var array<string, float> seconds a tier takes */
    public array $seconds = [];
    /** @var array<int, array{tier: Tier, ctx: ComputeContext}> */
    public array $calls = [];
    /** @var callable|null fn(Tier, ComputeContext) run during compute, e.g. to look at the locks */
    public $during = null;

    public function __construct(private readonly ?FixedClock $clock = null)
    {
    }

    public function compute(Tier $tier, ComputeContext $ctx): TierResult
    {
        $this->calls[] = ['tier' => $tier, 'ctx' => $ctx];
        if ($this->during !== null) {
            ($this->during)($tier, $ctx);
        }
        if ($this->clock !== null) {
            $this->clock->advance($this->seconds[$tier->value] ?? 0.01);
        }
        $result = $this->results[$tier->value] ?? new TierResult([$tier->value . 'Value' => 1], $ctx->now, false);
        if ($result instanceof \Throwable) {
            throw $result;
        }
        return $result;
    }

    /** @return string[] tier values computed, in order */
    public function computedTiers(): array
    {
        return array_map(static fn (array $call): string => $call['tier']->value, $this->calls);
    }
}
