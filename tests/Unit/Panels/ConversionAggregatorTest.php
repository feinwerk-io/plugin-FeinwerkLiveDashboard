<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Panels;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Panels\ConversionAggregator;

/**
 * @group FeinwerkLiveDashboard
 */
class ConversionAggregatorTest extends TestCase
{
    private const WINDOWS = ['3' => '2026-11-18 11:57:00', '60' => '2026-11-18 11:00:00'];
    private const EMPTY = ['conversions' => 0, 'revenue' => 0.0, 'orders' => 0, 'orderRevenue' => 0.0, 'goals' => []];

    private static function aggregate(array $rows): array
    {
        $aggregator = new ConversionAggregator(self::WINDOWS);
        foreach ($rows as [$idGoal, $revenue, $time]) {
            $aggregator->add(['idgoal' => $idGoal, 'revenue' => $revenue, 'server_time' => $time]);
        }
        return $aggregator->result();
    }

    public function test_noRows_givesZeros(): void
    {
        $this->assertSame(['3' => self::EMPTY, '60' => self::EMPTY], self::aggregate([]));
    }

    public function test_goalsAndOrders_perWindow(): void
    {
        $r = self::aggregate([
            [1, '0', '2026-11-18 11:59:00'],
            [1, '5.5', '2026-11-18 11:30:00'],
            [2, null, '2026-11-18 11:20:00'],
            [0, '49.90', '2026-11-18 11:58:00'],
            [0, '10.10', '2026-11-18 11:10:00'],
        ]);

        $this->assertSame(['conversions' => 2, 'revenue' => 49.9, 'orders' => 1, 'orderRevenue' => 49.9, 'goals' => [[1, 1, 0.0]]], $r['3']);
        $this->assertSame(5, $r['60']['conversions']);
        $this->assertSame(65.5, $r['60']['revenue']);
        $this->assertSame(2, $r['60']['orders']);
        $this->assertSame(60.0, $r['60']['orderRevenue']);
        $this->assertSame([[1, 2, 5.5], [2, 1, 0.0]], $r['60']['goals']);
    }

    public function test_groupsCountedInTheDatabase_addUpPerWindow(): void
    {
        $aggregator = new ConversionAggregator(['3' => '2026-11-18 11:57:00', '10' => '2026-11-18 11:50:00']);
        $aggregator->addGroup(['idgoal' => '1', 'w' => '3', 'conversions' => '2', 'revenue' => '10.5']);
        $aggregator->addGroup(['idgoal' => '1', 'w' => '10', 'conversions' => '3', 'revenue' => '4']);
        $aggregator->addGroup(['idgoal' => '0', 'w' => '10', 'conversions' => '1', 'revenue' => '49.9']);
        $aggregator->addGroup(['idgoal' => '-1', 'w' => '3', 'conversions' => '5', 'revenue' => '9']);
        $r = $aggregator->result();

        $this->assertSame(2, $r['3']['conversions']);
        $this->assertSame([[1, 2, 10.5]], $r['3']['goals']);
        $this->assertSame(6, $r['10']['conversions']);
        $this->assertSame(1, $r['10']['orders']);
        $this->assertSame([[1, 5, 14.5]], $r['10']['goals']);
        $this->assertEqualsWithDelta(64.4, $r['10']['revenue'], 0.001);
    }

    public function test_abandonedCart_isIgnored(): void
    {
        $r = self::aggregate([[-1, '99', '2026-11-18 11:59:00'], [3, '1', '2026-11-18 11:59:00']]);
        $this->assertSame(1, $r['3']['conversions']);
        $this->assertSame(1.0, $r['3']['revenue']);
        $this->assertSame([[3, 1, 1.0]], $r['3']['goals']);
    }
}
