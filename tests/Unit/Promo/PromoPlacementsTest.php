<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Promo;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Promo\PromoLine;
use Piwik\Plugins\FeinwerkLiveDashboard\Promo\PromoPlacements;

/**
 * The free placements of the Feinwerk promotion kit: which message goes where, and the banner at the top of the admin
 * home only. Live Dashboard uses only the banner of them (Peter, 2026-11-19). The parts that need Matomo classes (the
 * claim in Matomo's transient cache, the box widget, the plugin's events) are in tests/Integration/PromoTest.php:
 * tests/Unit loads no Matomo code.
 *
 * @group Plugins
 * @group FeinwerkLiveDashboard
 */
class PromoPlacementsTest extends TestCase
{
    public function test_each_placement_shows_its_message(): void
    {
        $nothing = static function (string $plugin): bool {
            return false;
        };
        self::assertSame('hosting', PromoPlacements::messageFor(PromoPlacements::BANNER, $nothing));
        self::assertSame('campaigns', PromoPlacements::messageFor('campaigns', $nothing));
        self::assertSame('capi', PromoPlacements::messageFor('ecommerce', $nothing), 'the ecommerce page exists only with ecommerce');
        self::assertSame('sentinel', PromoPlacements::messageFor('goals', $nothing));
        self::assertNull(PromoPlacements::messageFor('line', $nothing), 'the tip line is not a placement of its own');
    }

    public function test_no_box_for_a_product_that_is_installed(): void
    {
        $sentinel = static function (string $plugin): bool {
            return $plugin === 'FeinwerkSentinel';
        };
        $capi = static function (string $plugin): bool {
            return $plugin === 'FeinwerkConversionsApi';
        };
        self::assertNull(PromoPlacements::messageFor('goals', $sentinel));
        self::assertSame('campaigns', PromoPlacements::messageFor('campaigns', $sentinel));
        self::assertNull(PromoPlacements::messageFor('ecommerce', $capi));
    }

    /** Every message belongs to a placement of the kit, and every placement has a message. */
    public function test_messages_and_placements_match(): void
    {
        $placements = array_merge([PromoPlacements::BANNER], array_keys(PromoPlacements::PAGES));
        $used = [];
        foreach (PromoLine::MESSAGES as $id => $message) {
            if ($message['placement'] === 'line') {
                self::assertSame(PromoLine::CONNECTOR_MESSAGE, $id, 'only the connector message is tip line only');
                continue;
            }
            self::assertContains($message['placement'], $placements, $id);
            $used[] = $message['placement'];
        }
        self::assertEqualsCanonicalizing($placements, $used);
    }

    /** The plugin never advertises itself (docs/promotions.md). */
    public function test_no_message_advertises_live_dashboard(): void
    {
        foreach (PromoLine::MESSAGES as $id => $message) {
            self::assertNotSame(PromoLine::PLUGIN, $message['product'], $id);
        }
    }

    public function test_the_box_is_not_offered_to_api_calls_with_a_token(): void
    {
        self::assertTrue(PromoPlacements::isApiCallWithoutSession('API', 0), 'a script or a Widgetize embed');
        self::assertFalse(PromoPlacements::isApiCallWithoutSession('API', 1), 'Matomo\'s own interface');
        self::assertFalse(PromoPlacements::isApiCallWithoutSession('CoreHome', 0));
    }

    /** Peter, 2026-11-13: "Banner auf der Admin-Startseite nach oben", at the top of the admin home and nowhere else. */
    public function test_the_banner_appears_at_the_top_of_the_admin_home_only(): void
    {
        $banner = static function (&$out): void {
            $out .= '<banner>';
        };

        $out = '<other plugin>';
        PromoPlacements::beforeContent($out, 'admin', 'CoreAdminHome', 'home', $banner);
        self::assertSame('<other plugin><banner>', $out);

        $elsewhere = [
            ['admin', 'CoreAdminHome', 'generalSettings'],
            ['admin', 'CoreAdminHome', 'trackingCodeGenerator'],
            ['admin', 'UsersManager', 'index'],
            ['admin', 'Marketplace', 'overview'],
            ['dashboard', 'CoreHome', 'index'],
            ['dashboard', 'CoreAdminHome', 'home'],
            ['dashboard', 'FeinwerkLiveDashboard', 'getLiveBoard'],
            [null, null, null],
        ];
        foreach ($elsewhere as [$type, $module, $action]) {
            $out = '';
            PromoPlacements::beforeContent($out, $type, $module, $action, $banner);
            self::assertSame('', $out, sprintf('no banner for %s/%s/%s', $type, $module, $action));
        }
    }
}
