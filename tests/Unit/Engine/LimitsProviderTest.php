<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\LimitsProvider;

/**
 * @group FeinwerkLiveDashboard
 */
class LimitsProviderTest extends TestCase
{
    public function test_defaults(): void
    {
        $limits = LimitsProvider::fromArrays([], []);

        $this->assertSame(10, $limits->fastSeconds);
        $this->assertSame(60, $limits->slowSeconds);
        $this->assertSame(300, $limits->todaySeconds);
        $this->assertSame(3, $limits->statementSeconds);
        $this->assertSame(8, $limits->budgetSeconds);
        $this->assertSame(50000, $limits->visitRowCap);
        $this->assertSame(100000, $limits->actionRowCap);
        $this->assertSame(2, $limits->maxConcurrent);
        $this->assertSame(20, $limits->maxActiveKeys);
        $this->assertSame(3, $limits->cityMinVisits);
        $this->assertSame(3, $limits->activeMinutes);
        $this->assertSame(20000, $limits->typeLookupCap);
        $this->assertTrue($limits->useReader);
    }

    public function test_configValues_areTaken(): void
    {
        $limits = LimitsProvider::fromArrays(
            ['fast_seconds' => '15', 'visit_row_cap' => '5000', 'use_reader' => '0', 'city_min_visits' => 5],
            []
        );

        $this->assertSame(15, $limits->fastSeconds);
        $this->assertSame(5000, $limits->visitRowCap);
        $this->assertFalse($limits->useReader);
        $this->assertSame(5, $limits->cityMinVisits);
    }

    public function test_activeMinutes_followsMatomoAndIsClamped(): void
    {
        $this->assertSame(5, LimitsProvider::fromArrays([], ['live_widget_visitor_count_last_minutes' => '5'])->activeMinutes);
        $this->assertSame(1, LimitsProvider::fromArrays([], ['live_widget_visitor_count_last_minutes' => '0'])->activeMinutes);
        $this->assertSame(30, LimitsProvider::fromArrays([], ['live_widget_visitor_count_last_minutes' => 2880])->activeMinutes);
        $this->assertSame(3, LimitsProvider::fromArrays([], ['live_widget_visitor_count_last_minutes' => 'x'])->activeMinutes);
    }

    public function test_invalidValues_fallBackToDefaults(): void
    {
        $this->assertSame(10, LimitsProvider::fromArrays(['fast_seconds' => 'abc'], [])->fastSeconds);
        $this->assertSame(10, LimitsProvider::fromArrays(['fast_seconds' => '-5'], [])->fastSeconds);
        $this->assertSame(10, LimitsProvider::fromArrays(['fast_seconds' => ['array']], [])->fastSeconds);
        $this->assertSame(10, LimitsProvider::fromArrays(['fast_seconds' => '1.5'], [])->fastSeconds);
        $this->assertTrue(LimitsProvider::fromArrays(['use_reader' => 'yes'], [])->useReader);
    }

    public function test_lowerBounds(): void
    {
        $this->assertSame(5, LimitsProvider::fromArrays(['fast_seconds' => 2], [])->fastSeconds);
        $this->assertSame(1, LimitsProvider::fromArrays(['statement_seconds' => 0], [])->statementSeconds);
        $this->assertSame(1, LimitsProvider::fromArrays(['max_concurrent' => 0], [])->maxConcurrent);
        $this->assertSame(30, LimitsProvider::fromArrays(['slow_seconds' => 1], [])->slowSeconds);
        $this->assertSame(1, LimitsProvider::fromArrays(['visit_row_cap' => 0], [])->visitRowCap);
    }

    public function test_upperBounds(): void
    {
        $this->assertSame(300, LimitsProvider::fromArrays(['fast_seconds' => 100000], [])->fastSeconds);
        $this->assertSame(60, LimitsProvider::fromArrays(['statement_seconds' => 3600], [])->statementSeconds);
        $this->assertSame(60, LimitsProvider::fromArrays(['budget_seconds' => 3600], [])->budgetSeconds);
        $this->assertSame(20, LimitsProvider::fromArrays(['max_concurrent' => 500], [])->maxConcurrent);
    }
}
