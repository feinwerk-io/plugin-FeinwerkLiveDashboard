<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Panels;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Panels\TopList;

/**
 * @group FeinwerkLiveDashboard
 */
class TopListTest extends TestCase
{
    public function test_top_sortsByCountThenKey(): void
    {
        $this->assertSame([[7, 5], [2, 3], [9, 3]], TopList::top([9 => 3, 7 => 5, 2 => 3, 4 => 1], 3));
        $this->assertSame([['de', 2], ['at', 1], ['ch', 1]], TopList::top(['ch' => 1, 'de' => 2, 'at' => 1], 10));
    }

    public function test_top_breaksTiesByKeyNumerically(): void
    {
        $this->assertSame([[9, 1], [10, 1]], TopList::top([10 => 1, 9 => 1], 5));
    }

    public function test_top_ofNothing_isEmpty(): void
    {
        $this->assertSame([], TopList::top([], 5));
        $this->assertSame([], TopList::top([1 => 2], 0));
    }
}
