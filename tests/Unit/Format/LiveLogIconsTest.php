<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Format;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Format\LiveLogIcons;

/**
 * The fixed icon column of the live log: one place per kind of icon, Matomo's own images.
 *
 * @group FeinwerkLiveDashboard
 */
class LiveLogIconsTest extends TestCase
{
    public function test_flags_comeFromTheVisitRow(): void
    {
        $row = ['visit_goal_converted' => 1, 'visit_goal_buyer' => 0, 'visitor_returning' => 0];
        $this->assertSame(['goal'], LiveLogIcons::flags($row));
        $this->assertSame(['order'], LiveLogIcons::flags(['visit_goal_converted' => 1, 'visit_goal_buyer' => 1, 'visitor_returning' => 0]),
            'an order also sets visit_goal_converted: the order icon says it');
        $this->assertSame(['cart'], LiveLogIcons::flags(['visit_goal_converted' => 0, 'visit_goal_buyer' => 2, 'visitor_returning' => 0]));
        $this->assertSame(['order', 'cart', 'returning'], LiveLogIcons::flags(['visit_goal_converted' => 1, 'visit_goal_buyer' => 3, 'visitor_returning' => 1]));
        $this->assertSame([], LiveLogIcons::flags([]));
    }

    public function test_definitions_haveFixedPlacesAndMatomosImages(): void
    {
        $t = static fn (string $key): string => 'T:' . $key;
        $this->assertSame([
            ['key' => 'goal', 'src' => 'plugins/Morpheus/images/goal.svg', 'label' => 'T:FeinwerkLiveDashboard_LogGoal'],
            ['key' => 'order', 'src' => 'plugins/Morpheus/images/ecommerceOrder.svg', 'label' => 'T:FeinwerkLiveDashboard_LogOrder'],
            ['key' => 'cart', 'src' => 'plugins/Morpheus/images/ecommerceAbandonedCart.svg', 'label' => 'T:FeinwerkLiveDashboard_LogCart'],
            ['key' => 'returning', 'src' => 'plugins/Live/images/returningVisitor.png', 'label' => 'T:FeinwerkLiveDashboard_LogReturning'],
        ], LiveLogIcons::definitions(true, $t, []));
        $this->assertSame(['goal', 'returning'], array_column(LiveLogIcons::definitions(false, $t, []), 'key'), 'no shop: no order or cart places');
    }

    public function test_otherPlugins_addPlacesAfterTheOwnOnes(): void
    {
        $t = static fn (string $key): string => $key;
        $extra = [
            ['key' => 'form', 'src' => 'plugins/Other/form.svg', 'label' => 'Form'],
            ['key' => 'goal', 'src' => 'evil.svg', 'label' => 'replaces nothing'],
            ['key' => 'bad key!', 'src' => 'x.svg', 'label' => 'refused'],
            ['key' => 'ext', 'src' => 'https://example.org/x.svg', 'label' => 'refused: only images inside Matomo'],
        ];
        $defs = LiveLogIcons::definitions(false, $t, $extra);
        $this->assertSame(['goal', 'returning', 'form'], array_column($defs, 'key'));
        $this->assertSame('plugins/Morpheus/images/goal.svg', $defs[0]['src']);
    }
}
