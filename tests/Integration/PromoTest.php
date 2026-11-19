<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Integration;

use Matomo\Cache\Transient;
use Piwik\Cache;
use Piwik\Config;
use Piwik\Plugins\FeinwerkLiveDashboard\FeinwerkLiveDashboard;
use Piwik\Plugins\FeinwerkLiveDashboard\Promo\PromoCard;
use Piwik\Plugins\FeinwerkLiveDashboard\Promo\PromoLine;
use Piwik\Plugins\FeinwerkLiveDashboard\Promo\PromoPlacements;
use Piwik\Plugins\FeinwerkLiveDashboard\Widgets\GetLiveBoard;
use Piwik\Plugins\FeinwerkLiveDashboard\Widgets\GetLiveMap;
use Piwik\Plugins\FeinwerkLiveDashboard\Widgets\GetLiveOverview;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\View;
use Piwik\Widget\WidgetConfig;

/**
 * The Feinwerk promotion kit in Live Dashboard (Peter, 2026-11-19): the tip line under the board's own page, the card in
 * its dashboard widgets, the banner on the admin home; only for admins and super users, never in Widgetize embeds, off
 * with every switch. The rotation turns with the date, so the tests check the markup, never a message.
 *
 * @group FeinwerkLiveDashboard
 */
class PromoTest extends IntegrationTestCase
{
    private const LINE = 'class="fwpromo-line"';
    private const CARD = 'class="fwpromo-card"';
    private const CARD_ID = '/data-fwpromo-id="[a-z_]+-card"/';
    private const RAW_KEY = '/FeinwerkLiveDashboard_(Promo|Premium)\w+/';

    /** @var array<string, mixed> */
    private $ownSection = [];
    /** @var array<string, mixed> */
    private $connectorSection = [];

    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        Fixture::loadAllTranslations();
        FakeAccess::$superUser = true;
        Fixture::createWebsite('2026-01-01 00:00:00', 0, 'Shop', 'https://example.com');
        FakeAccess::clearAccess(true);
        $_GET['idSite'] = '1';

        // whatever the test environment says: promotions on for this plugin and for all Feinwerk plugins
        $config = Config::getInstance();
        $this->ownSection = is_array($config->FeinwerkLiveDashboard) ? $config->FeinwerkLiveDashboard : [];
        $this->connectorSection = is_array($config->FeinwerkMarketplaceConnector) ? $config->FeinwerkMarketplaceConnector : [];
        $config->FeinwerkLiveDashboard = array_merge($this->ownSection, ['promotions' => '1']);
        $config->FeinwerkMarketplaceConnector = array_merge($this->connectorSection, ['promotions' => '1']);
        Cache::getTransientCache()->flushAll();
    }

    public function tearDown(): void
    {
        $config = Config::getInstance();
        $config->FeinwerkLiveDashboard = $this->ownSection;
        $config->FeinwerkMarketplaceConnector = $this->connectorSection;
        unset($_GET['idSite'], $_GET['module'], $_GET['disableLink'], $_GET['widget']);
        Cache::getTransientCache()->flushAll();
        Fixture::resetTranslations();
        parent::tearDown();
    }

    public function test_aSuperUser_seesTheTipLineUnderTheBoard(): void
    {
        $html = (new GetLiveBoard())->render();

        $this->assertStringContainsString(self::LINE, $html);
        $this->assertStringContainsString('data-fwpromo-placement="line"', $html);
        $this->assertStringContainsString('class="fwlogo"', $html, 'the line starts with the Feinwerk logo');
        $this->assertStringContainsString('Hide this tip for 30 days', $html);
        $this->assertSame(0, preg_match(self::RAW_KEY, $html), 'every text is translated');
        $this->assertStringNotContainsString(self::CARD, $html, 'no card on the board\'s page');
        $this->assertGreaterThan(strpos($html, 'vue-entry="FeinwerkLiveDashboard.LiveBoard"'), strpos($html, self::LINE), 'after the board');
        $this->assertSame(1, substr_count($html, self::LINE));
    }

    /**
     * The super user's connector link stays inside Matomo (same tab, docs/promo-kit.md); a link to feinwerk.io opens a new
     * tab. Regression: the kit's "external|default(true)" turned false into true (Twig's default filter replaces every
     * empty value), so the in-Matomo link opened in a new tab.
     */
    public function test_aLinkInsideMatomo_opensInTheSameTab(): void
    {
        $translate = static function (string $key): string {
            return $key;
        };
        $inside = PromoLine::variables('connector', 'en', $translate, 'line', false, ['superUser' => true, 'marketplace' => true]);
        $outside = PromoLine::variables('campaigns', 'en', $translate);
        $this->assertFalse($inside['external']);

        foreach (['_promo' => 'promo', '_promoCard' => 'promoCard'] as $template => $variable) {
            foreach ([[$inside, false], [$outside, true]] as [$line, $newTab]) {
                $view = new View('@FeinwerkLiveDashboard/' . $template);
                $view->$variable = $variable === 'promo' ? $line : PromoCard::fromLine($line, 'Learn more');
                $html = $view->render();
                $this->assertSame($newTab ? 1 : 0, substr_count($html, 'target="_blank"'), $template . ' ' . $line['id']);
            }
        }
    }

    public function test_anAdmin_seesItToo(): void
    {
        FakeAccess::clearAccess(false, [1], [], 'admin');

        $html = (new GetLiveBoard())->render();

        $this->assertStringContainsString(self::LINE, $html);
        $this->assertSame(0, preg_match(self::RAW_KEY, $html));
    }

    public function test_aViewer_seesNoPromotion(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');

        $this->assertStringNotContainsString('fwpromo', (new GetLiveBoard())->render());
        $this->assertStringNotContainsString('fwpromo', (new GetLiveOverview())->render());
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function embeds(): array
    {
        return [
            'Widgetize' => ['module', 'Widgetize'],
            'disableLink' => ['disableLink', '1'],
        ];
    }

    /**
     * @dataProvider embeds
     */
    public function test_nothingInAWidgetizeEmbed(string $parameter, string $value): void
    {
        $_GET[$parameter] = $value;

        $this->assertStringNotContainsString('fwpromo', (new GetLiveBoard())->render());
        $_GET['widget'] = '1';
        $this->assertStringNotContainsString('fwpromo', (new GetLiveBoard())->render());
        $this->assertStringNotContainsString('fwpromo', (new GetLiveOverview())->render());
    }

    public function test_aDashboardWidget_showsTheCardAndNoTipLine(): void
    {
        $_GET['widget'] = '1';

        foreach ([new GetLiveOverview(), new GetLiveMap()] as $widget) {
            $html = $widget->render();
            $this->assertStringContainsString(self::CARD, $html, get_class($widget));
            $this->assertSame(1, preg_match(self::CARD_ID, $html), 'a dismissal key of its own');
            $this->assertStringContainsString('class="fwpromo-card__close" title="Hide this tip for 30 days"', $html);
            $this->assertStringNotContainsString('class="fwpromo-line', $html);
            $this->assertStringNotContainsString('fwlogo', $html, 'no logo in the card');
            $this->assertSame(0, preg_match(self::RAW_KEY, $html));
            $this->assertGreaterThan(strpos($html, 'vue-entry="FeinwerkLiveDashboard.LiveBoard"'), strpos($html, self::CARD), 'after the board');
        }
    }

    /** The whole board on a dashboard is a dashboard widget too: the card, never the tip line (house rule, docs/footer.md). */
    public function test_theWholeBoardOnADashboard_showsTheCard(): void
    {
        $_GET['widget'] = '1';

        $html = (new GetLiveBoard())->render();

        $this->assertStringContainsString(self::CARD, $html);
        $this->assertStringNotContainsString('class="fwpromo-line', $html);
    }

    public function test_offWithThePluginsSwitch(): void
    {
        Config::getInstance()->FeinwerkLiveDashboard = array_merge($this->ownSection, ['promotions' => '0']);

        $this->assertStringNotContainsString('fwpromo', (new GetLiveBoard())->render());
        $_GET['widget'] = '1';
        $this->assertStringNotContainsString('fwpromo', (new GetLiveOverview())->render());
        $out = '';
        (new FeinwerkLiveDashboard())->addPromoBanner($out, 'admin', 'CoreAdminHome', 'home');
        $this->assertSame('', $out);
    }

    public function test_offWithTheSwitchForAllFeinwerkPlugins(): void
    {
        Config::getInstance()->FeinwerkMarketplaceConnector = ['promotions' => '0'];

        $this->assertStringNotContainsString('fwpromo', (new GetLiveBoard())->render());
        $out = '';
        (new FeinwerkLiveDashboard())->addPromoBanner($out, 'admin', 'CoreAdminHome', 'home');
        $this->assertSame('', $out);
    }

    public function test_theBanner_atTheTopOfTheAdminHome(): void
    {
        $plugin = new FeinwerkLiveDashboard();

        $out = '';
        $plugin->addPromoBanner($out, 'admin', 'CoreAdminHome', 'home');
        $this->assertStringContainsString('fwpromo-box--banner', $out);
        $this->assertStringContainsString('data-fwpromo-placement="adminHome"', $out);
        $this->assertStringContainsString('Tip from Feinwerk', $out);
        $this->assertStringContainsString('class="fwlogo"', $out, 'banners carry the Feinwerk logo');
        $this->assertStringContainsString('mtm_source=FeinwerkLiveDashboard', $out);
        $this->assertStringNotContainsString('data-fwpromo-widget', $out, 'the banner is no widget of its own');
        $this->assertSame(0, preg_match(self::RAW_KEY, $out));

        $again = '';
        $plugin->addPromoBanner($again, 'admin', 'CoreAdminHome', 'generalSettings');
        $this->assertSame('', $again, 'only on the admin home');
    }

    public function test_theBanner_notForAViewer(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');

        $out = '';
        (new FeinwerkLiveDashboard())->addPromoBanner($out, 'admin', 'CoreAdminHome', 'home');
        $this->assertSame('', $out);
    }

    /** Peter, 2026-11-13: "nur ein Banner, Connector-Empfehlung zuerst": a placement the connector claimed stays its own. */
    public function test_theBanner_yieldsToTheConnectorsClaim(): void
    {
        Cache::getTransientCache()->save(PromoPlacements::CLAIM_KEY . PromoPlacements::BANNER, 'FeinwerkMarketplaceConnector');

        $out = '';
        (new FeinwerkLiveDashboard())->addPromoBanner($out, 'admin', 'CoreAdminHome', 'home');
        $this->assertSame('', $out);
    }

    /** Once per page across all Feinwerk plugins: the first claims, the same plugin may ask again, another may not. */
    public function test_theFirstPluginClaimsAPlacementForTheRequest(): void
    {
        $cache = new Transient();
        $this->assertTrue(PromoPlacements::claim('adminHome', 'FeinwerkLiveDashboard', $cache));
        $this->assertTrue(PromoPlacements::claim('adminHome', 'FeinwerkLiveDashboard', $cache), 'the page asks again');
        $this->assertFalse(PromoPlacements::claim('adminHome', 'FeinwerkSentinel', $cache));
        $this->assertTrue(PromoPlacements::claim('goals', 'FeinwerkSentinel', $cache), 'placements are claimed one by one');
        $this->assertSame('FeinwerkLiveDashboard', $cache->fetch('fwpromo.claimed.adminHome'), 'the shared key of all Feinwerk plugins');
    }

    /** The kit's box widget stays unwired here, but stays what the kit says it is (never a widget for dashboards). */
    public function test_theKitsBoxWidget(): void
    {
        $config = PromoPlacements::boxConfig('ecommerce', 'capi', 'Tip from Feinwerk');
        $this->assertSame(['module' => 'FeinwerkLiveDashboard', 'action' => 'promoBox', 'fwpromo' => 'capi'], $config->getParameters());
        $this->assertFalse($config->isWidgetizeable());

        $report = new WidgetConfig();
        $report->setCategoryId('Referrers_Referrers');
        $report->setSubcategoryId('Referrers_Campaigns');
        $report->setModule('Referrers');
        $report->setAction('getCampaigns');
        $this->assertSame(['Referrers_Referrers/Referrers_Campaigns' => true], PromoPlacements::pagesWithWidgets([$report, $config]));
    }

    /**
     * The banner hangs on Template.beforeContent (Template.adminHome renders below Matomo's Marketplace blocks); no
     * context boxes on report pages (Peter, 2026-11-19); the kit's scripts and styles are registered and exist.
     */
    public function test_theEventsAndAssetsOfThePlugin(): void
    {
        $plugin = new FeinwerkLiveDashboard();
        $events = $plugin->registerEvents();

        $this->assertSame('addPromoBanner', $events['Template.beforeContent'] ?? null);
        $this->assertArrayNotHasKey('Template.adminHome', $events);
        $this->assertArrayNotHasKey('Widget.filterWidgets', $events, 'no context boxes');
        $this->assertFalse(method_exists('Piwik\\Plugins\\FeinwerkLiveDashboard\\Controller', 'promoBox'));

        $js = [];
        $plugin->getJsFiles($js);
        $css = [];
        $plugin->getStylesheetFiles($css);
        $prefix = 'plugins/FeinwerkLiveDashboard/';
        foreach (['javascripts/promo.js', 'javascripts/promocard.js'] as $file) {
            $this->assertContains($prefix . $file, $js);
        }
        foreach (['stylesheets/promo.css', 'stylesheets/promocard.css', 'stylesheets/feinwerklogo.css'] as $file) {
            $this->assertContains($prefix . $file, $css);
        }
        foreach (array_merge($js, $css) as $file) {
            $this->assertFileExists(dirname(__DIR__, 2) . '/' . substr($file, strlen($prefix)));
        }
    }
}
