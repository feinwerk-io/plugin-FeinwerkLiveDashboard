<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Budget;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Sampling;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes\FixedClock;

/**
 * @group FeinwerkLiveDashboard
 */
class SamplingAndBudgetTest extends TestCase
{
    public function test_factor_isOneUpToTheCap(): void
    {
        $this->assertSame(1, Sampling::factor(0, 100000));
        $this->assertSame(1, Sampling::factor(100000, 100000));
    }

    public function test_factor_roundsUp(): void
    {
        $this->assertSame(2, Sampling::factor(100001, 100000));
        $this->assertSame(3, Sampling::factor(200001, 100000));
        $this->assertSame(7, Sampling::factor(7, 1));
    }

    public function test_factor_withoutUsableCap_isOneRowPerCount(): void
    {
        $this->assertSame(5, Sampling::factor(5, 0));
    }

    public function test_budget_exhaustsOnFixedClock(): void
    {
        $clock = new FixedClock(1000.0);
        $budget = new Budget($clock, 8.0);

        $this->assertSame(8.0, $budget->remaining());
        $this->assertFalse($budget->exhausted());

        $clock->advance(7.5);
        $this->assertSame(0.5, $budget->remaining());
        $this->assertSame(7.5, $budget->elapsed());

        $clock->advance(1.0);
        $this->assertSame(0.0, $budget->remaining());
        $this->assertTrue($budget->exhausted());
    }
}
